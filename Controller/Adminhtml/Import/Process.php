<?php
namespace Expertime\Import\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception;

class Process extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Expertime_Import::import';

    /*
    *   @var \Expertime\Import\Model\Curl
    */    
    protected $_curl;

    /*
    *   @var \Expertime\Import\Api\ImportInterface
    */
    protected $_import;

    protected $_messageManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Expertime\Import\Model\Curl $curl,
        \Expertime\Import\Api\ImportManagementInterface $import
    ){
        $this->_curl = $curl;
        $this->_import = $import;
        $this->_messageManager = $context->getMessageManager();
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * Import Process action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try{
            $result = $this->_import->createCustomers($this->_curl->getAllUsers());
            if($result){
                $this->messageManager->addSuccess('Users are imported completely.');
            }else{
                $this->messageManager->addError('Users are not imported completely. Please check system log');
            }
        }catch(StateException $e){
            $this->_messageManager->addError(__('API Error: %s', $e->getMessage()));
        }catch(InvalidArgumentException $e){
            $this->_messageManager->addError(__('Invalid arguemnts: %s', $e->getMessage()));
        }catch(Exception $e){
            $this->_messageManager->addError(__('Exception: %s', $e->getMessage()));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*');
        return $resultRedirect;
    }
}
