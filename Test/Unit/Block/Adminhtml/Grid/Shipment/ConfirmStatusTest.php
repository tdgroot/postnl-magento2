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
namespace TIG\PostNL\Test\Unit\Block\Adminhtml\Grid\Shipment;

use TIG\PostNL\Test\TestCase;
use TIG\PostNL\Block\Adminhtml\Grid\Shipment\ConfirmStatus;

class ConfirmStatusTest extends TestCase
{
    protected $instanceClass = ConfirmStatus::class;

    public function getIsConfirmedProvider()
    {
        return [
            'exists_but_not_confirmed' => [null, false],
            'exists_and_confirmed' => ['2016-11-19 21:13:12', true],
        ];
    }

    /**
     * @param $confirmedAt
     * @param $expected
     *
     * @dataProvider getIsConfirmedProvider
     */
    public function testGetCellContents($confirmedAt, $expected)
    {
        $item = ['tig_postnl_confirmed_at' => $confirmedAt];

        $instance = $this->getFakeMock($this->instanceClass)->getMock();

        /** @var \Magento\Framework\Phrase $result */
        $result = $this->invokeArgs('getCellContents', [$item], $instance);

        $this->assertInstanceOf(\Magento\Framework\Phrase::class, $result);
        $text = ucfirst(($expected ? '' : 'not ') . 'confirmed');
        $this->assertEquals($text, $result->getText());
    }
}
