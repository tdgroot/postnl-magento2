<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\PostNL\Service\Shipment;

use TIG\PostNL\Config\Provider\Globalpack;
use TIG\PostNL\Api\Data\ShipmentInterface;
use TIG\PostNL\Service\Shipment\Customs\SortItems;
use TIG\PostNL\Service\Shipment\Customs\AttributeValues;

class Customs
{
    /**
     * @var Globalpack
     */
    private $globalpackConfig;

    /**
     * @var SortItems
     */
    private $sortItems;

    private $attributeValues;

    private $customs = [
        'ShipmentType'           => 'Commercial Goods',
        'HandleAsNonDeliverable' => 'false',
        'Invoice'                => 'false',
        'Certificate'            => 'false',
        'License'                => 'false',
        'Currency'               => 'EUR',
    ];

    /**
     * @var \Magento\Sales\Api\Data\ShipmentInterface
     */
    private $shipment;

    private $requiredInvoiceTypes = [
        'Commercial Goods',
        'Commercial Sample',
        'Returned Goods',
    ];

    public function __construct(
        Globalpack $globalpack,
        SortItems $sortItems,
        AttributeValues $attributeValues
    ) {
        $this->globalpackConfig = $globalpack;
        $this->sortItems = $sortItems;
        $this->attributeValues = $attributeValues;
    }

    /**
     * @param ShipmentInterface $postnlShipment
     *
     * @return array
     */
    public function get(ShipmentInterface $postnlShipment)
    {
        $this->shipment = $postnlShipment->getShipment();

        $this->setCustomsLicense();
        $this->setCustomsCertificate();
        $this->setShipmentType();
        $this->setContentInformation();

        return $this->customs;
    }

    private function setCustomsCertificate()
    {
        $certificate = $this->globalpackConfig->getCertificateNumber($this->shipment->getStoreId());
        if ($certificate) {
            $this->customs['Certificate']   = 'true';
            $this->customs['CertificateNr'] = $certificate;
        }
    }

    private function setCustomsLicense()
    {
        $license = $this->globalpackConfig->getLicenseNumber($this->shipment->getStoreId());
        if ($license) {
            $this->customs['License']   = 'true';
            $this->customs['LicenseNr'] = $license;
        }
    }

    private function setShipmentType()
    {
        $type = $this->globalpackConfig->getDefaultShipmentType($this->shipment->getStoreId());
        if ($type) {
            $this->customs['ShipmentType'] = $type;
        }

        if (in_array($this->customs['ShipmentType'], $this->requiredInvoiceTypes)
            || $this->customs['License'] == 'false'
            || $this->customs['Certificate'] == 'false'
        ) {
            $this->customs['Invoice']   = 'true';
            $this->customs['InvoiceNr'] = $this->shipment->getIncrementId();
        }
    }

    private function setContentInformation()
    {
        $content = [];
        /** @var \Magento\Sales\Model\Order\Shipment\Item $item */
        foreach ($this->sortItems->get($this->shipment) as $item) {
            $content[] = [
                'Description'     => $this->attributeValues->getDescription($item, $this->shipment->getStoreId()),
                'Quantity'        => $item->getQty(),
                'Weight'          => $item->getWeight() * $item->getQty(),
                'Value'           => $this->attributeValues->getCustomsValue($item, $this->shipment->getStoreId()),
                'HSTariffNr'      => $this->attributeValues->getHsTariff($item, $this->shipment->getStoreId()),
                'CountryOfOrigin' => $this->attributeValues->getCountryOfOrigin($item, $this->shipment->getStoreId())
            ];
        }

        $this->customs['Content'] = $content;
    }
}
