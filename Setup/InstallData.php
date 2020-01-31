<?php
namespace Expertime\Import\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig       = $eavConfig;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /*
            create customer avatar field
        */

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Customer::ENTITY,
            "expertime_avatar",
            [
                'type'       => 'varchar',
                'label'      => 'Avatar',
                'input'      => 'text',
                'validate_rules' => '',
                'source'     => '',
                'visible' => true,
                'required'   => false,
                'default'    => '',
                'sort_order' => 200,
                'system'     => false,
                'position'   => 200,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => false,   
                'is_searchable_in_grid' => false                    
            ]
        );
        $avatar = $this->eavConfig->getAttribute(Customer::ENTITY, "expertime_avatar");
        $avatar->setData(
            'used_in_forms',
            ['customer_account_create','customer_account_edit','adminhtml_customer']
        );
        $avatar->getResource()->save($avatar);

        $setup->endSetup();
    }
}