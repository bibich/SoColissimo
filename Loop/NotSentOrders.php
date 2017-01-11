<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace SoColissimo\Loop;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use SoColissimo\Model\Map\OrderAddressSocolissimoTableMap;
use SoColissimo\SoColissimo;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Loop\Order;
use Thelia\Model\Map\OrderTableMap;
use Thelia\Model\Order as OrderModel;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;

/**
 * Class NotSentOrders
 * @package SoColissimo\Loop
 * @author Thelia <info@thelia.net>
 */
class NotSentOrders extends Order
{
    public function getArgDefinitions()
    {
        return new ArgumentCollection(Argument::createBooleanTypeArgument('with_prev_next_info', false));
    }

    /**
     * this method returns a Propel ModelCriteria
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria()
    {
        $status = OrderStatusQuery::create()
            ->filterByCode(
                array(
                    OrderStatus::CODE_PAID,
                    OrderStatus::CODE_PROCESSING,
                ),
                Criteria::IN
            )
            ->find()
            ->toArray("code");
        $query = OrderQuery::create()
            ->filterByDeliveryModuleId(SoColissimo::getModCode())
            ->filterByStatusId(
                array(
                    $status[OrderStatus::CODE_PAID]['Id'],
                    $status[OrderStatus::CODE_PROCESSING]['Id']),
                Criteria::IN
            );

        // join with colissimo address
        $soColissimoAddress = new Join();
        $soColissimoAddress->addExplicitCondition(OrderTableMap::TABLE_NAME, 'DELIVERY_ORDER_ADDRESS_ID', 'order', OrderAddressSocolissimoTableMap::TABLE_NAME, 'ID', 'soc_address');
        $soColissimoAddress->setJoinType(Criteria::INNER_JOIN);
        $query->addJoinObject($soColissimoAddress);
        $query->withColumn('soc_address.Type', 'socolissimo_type');
        $query->withColumn('soc_address.Code', 'socolissimo_code');

        return $query;
    }

    /**
     * Use this method in order to add fields in sub-classes
     * @param LoopResultRow $loopResultRow
     * @param object|array $item
     *
     */
    protected function addOutputFields(LoopResultRow $loopResultRow, $item)
    {
        parent::addOutputFields($loopResultRow, $item);

        /** @var OrderModel $order */
        $order = $loopResultRow->model;
        if ($order->hasVirtualColumn('socolissimo_type')) {
            $loopResultRow->set('SOCOLISSIMO_TYPE', $order->getVirtualColumn('socolissimo_type'));
        }
        if ($order->hasVirtualColumn('socolissimo_code')) {
            $code = $order->getVirtualColumn('socolissimo_code');
            $loopResultRow->set('SOCOLISSIMO_CODE', empty($code) ? '' : $code);
        }
    }

}
