<?php
namespace Expertime\Import\Model;

use Magento\Framework\Exception;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class ImportManagement implements \Expertime\Import\Api\ImportManagementInterface
{ 
    /*
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $_store;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /*
    * @var \Magento\Customer\Api\CustomerRepositoryInterface
    */
    protected $_customerRepository;   

    /*
    * @var \Magento\Framework\Api\SearchCriteriaBuilder
    */
    protected $_searchCriteriaBuilder;  

    /*
    * @var \Magento\Framework\Encryption\EncryptorInterface
    */
    protected $_encryption;  

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /*
    * @var \Psr\Log\LoggerInterface
    */
    protected $_logger;  

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository, 
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,        
        DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryption,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_store = $storeManager;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_customerRepository = $customerRepository;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_encryption = $encryption; 
        $this->_logger = $logger;
    }
    /*
    * create/update user account from API
    * @var array $data
    * @return bool
    */
    public function createCustomers($data){
        if(!isset($data['data']) || count($data['data']) == 0 )
            return false;

        try{

            //extracts email
            $emails = array_column($data['data'], 'email');

            //search existing customers
            $searchCriteria = $this->_searchCriteriaBuilder->addFilter(
              'email',
              $emails,
              'in'
            )->create();
            $result = $this->_customerRepository->getList($searchCriteria);
            $update_email = [];
            $new_customers = [];

            //user exists, update the field : firstname, lastname and avatar
            foreach ($result->getItems() as $key => $customer) {
                $customer->setFirstname($data['data'][$key]['firstname']);
                $customer->setLastname($data['data'][$key]['lastname']);
                $customer->setCustomAttribute('expertime_avatar',$data['data'][$key]['avatar']);
                $this->_customerRepository->save($customer);
                $update_email[] = $customer->getEmail();
            }

            //filter new customer
            foreach($data['data'] as $var){
                if(isset($var['email']) && !in_array($var['email'], $update_email)){
                    $new_customers[] = $var;
                }
            }

            //otherwise, create customers
            foreach($new_customers as $customer){
                $new_customer = $this->_customerDataFactory->create();
                $avatar = $customer['avatar'];
                unset($customer['avatar']);
                $this->_dataObjectHelper->populateWithArray(
                    $new_customer,
                    $customer,
                    '\Magento\Customer\Api\Data\CustomerInterface'
                );
                $new_customer->setCustomAttribute('expertime_avatar',$avatar); 

                $this->_customerRepository->save($new_customer);
            }
        
            
        }catch(Exception $e){
            //skip the rest of the update
            $this->_logger->critical('Error message', ['exception' => $e]);
            return false;
        }
        return true;
    }
}
