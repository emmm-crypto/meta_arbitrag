<?php
/**
 * Created by PhpStorm.
 * User: el
 * Date: 21.02.2018
 * Time: 18:39
 */

namespace ApiBundle\Service;
use Symfony\Component\DependencyInjection\ContainerInterface;


class Bittrex
{
    private $contailer;

    private $em;

    /**
     * Livecoin constructor.
     *
     * @param ContainerInterface $container
     * @param \Doctrine\ORM\EntityManager $em
     */
    function __construct(ContainerInterface $container, \Doctrine\ORM\EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function mainFunction(
        $name,
        $pair
    )
    {
        try {
            $db_record = $this
                ->em
                ->getRepository('ApiBundle:ApiKey')
                ->findOneBy(array(
                        "exchange" => "bittrex",
                        "users" => $name,
                    )
                );

            $bittrex = new \ccxt\bittrex();
            $bittrex->apiKey = $db_record->getKey();
            $bittrex->secret = $db_record->getSecretKey();

            $bittrex->load_markets(true);

            $orders = $bittrex->fetch_order_book($pair);

            $i = $j = 0;
            $bids = $asks = 0;
            foreach ($orders["bids"] as $key => $value) {
                $i++;
                $bids += $value[0];
            }
            $aver_bid = $bids / $i;

            foreach ($orders["asks"] as $key => $value) {
                $j++;
                $asks += $value[0];
            }
            $aver_ask = $asks / $j;

            return array(
                "bid" => $aver_bid,
                "ask" => $aver_ask,
            );
        } catch (\Exception $e) {
            return array(
                "bid" => $e->getMessage(),
                "ask" => $e->getMessage(),
            );
        }

    }

}