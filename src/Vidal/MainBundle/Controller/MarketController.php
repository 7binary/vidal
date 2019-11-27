<?php

namespace Vidal\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Tests\Extension\Core\DataTransformer\DateTimeToArrayTransformerTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Vidal\MainBundle\Entity\MarketOrder;

use Vidal\MainBundle\Market\Product;
use Vidal\MainBundle\Market\FindDrug;
use Vidal\MainBundle\Market\Basket;
use Vidal\MainBundle\Geo\SxGeo;

class MarketController extends Controller
{
}
