<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\BasketBundle\Tests\Entity;

use Sonata\Component\Basket\BasketElement;

/**
 * @author Vincent Composieux <composieux@ekino.com>
 */
class BasketElementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test quantity setter, if quantity is negative, must returns a quantity of 1.
     */
    public function testNegativeQuantity()
    {
        $basketElement = new BasketElement();

        $basketElement->setQuantity(50);
        $this->assertEquals(50, $basketElement->getQuantity());

        $basketElement->setQuantity(-50);
        $this->assertEquals(1, $basketElement->getQuantity());
    }
}
