<?php
namespace Expertime\Import\Model;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Exception;


class Curl extends \Magento\Framework\Model\AbstractModel
{
    /**
     * System Configuration XML PATH
     *
     */
    const XML_USE_HTTPS_PATH = 'expertime_import/general/use_https';
    const XML_END_POINT = 'expertime_import/general/end_point';
    const XML_TIMEOUT = 'expertime_import/general/timeout';

    protected $_endPoint;
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     *
     */
    protected $_curlFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_backendConfig    = $backendConfig;
        $this->_curlFactory      = $curlFactory;
        $this->_urlBuilder       = $urlBuilder;
    }

    /**
     * Retrieve end point
     *
     * @return string
     */
    public function getEndPoint($page = 0)
    {
        if(!is_integer($page)){
            throw new InvalidArgumentException(__('getEndPoint only accepts integers.'));
        }
        $httpPath = $this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_endPoint === null) {
            $this->_endPoint = $httpPath . $this->_backendConfig->getValue(self::XML_END_POINT);
        }
        return $this->_endPoint . "?page=" . $page;
    }

    /**
     * Retrieve User data as JSON
     *
     * @return array[]
     */
    public function getUserData($page = 0)
    {
        try {
            $curl = $this->_curlFactory->create();
            $curl->setConfig(
                [
                    'timeout'   => $this->_backendConfig->getValue(self::XML_TIMEOUT),
                    'referer'   => $this->_urlBuilder->getUrl('*/*/*')
                ]
            );
            $curl->write(\Zend_Http_Client::GET, $this->getEndPoint($page), '1.0');
            $data = $curl->read();
            if ($data === false) {
                return false;
            }
            $data = preg_split('/^\r?$/m', $data, 2);
            $data = trim($data[1]);
            $curl->close();
            $json = \json_decode($data, true);
        } catch (\Exception $e) {
            throw $e;
        }

        if(!isset($json["data"]) || !is_array($json["data"]) || count($json["data"]) == 0){
            return false;
        }

        //change keyname
        foreach($json['data'] as $key => $var){
            $json['data'][$key]['firstname'] = $json['data'][$key]['first_name'];
            $json['data'][$key]['lastname'] = $json['data'][$key]['last_name'];
            unset($json['data'][$key]['id']);
            unset($json['data'][$key]['first_name']);
            unset($json['data'][$key]['last_name']);
        }
        

        return $json;
    }

    public function getAllUsers(){
        //initial call to retrieve total count of user
        $result = $this->getUserData();
        if(!isset($result['total']) ||!isset($result['page']) ){
            throw new StateException(__('Result payload is not matched.'));
        }
        $current_page = $result['page'];
        do{
            $result['data'] = array_merge($result['data'], $this->getUserData(++$current_page)['data']);
        }while($result['total_pages'] > $current_page);
        return $result;
    }
}
