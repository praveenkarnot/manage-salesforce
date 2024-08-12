<?php

namespace Drupal\custom_example\Controller;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\mysql\Driver\Database\mysql\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Menu\MenuTreeStorageInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\webform\Entity\WebformOptions;

/**
 * Class exampleController.
 */
class exampleSalesforceController extends ControllerBase
{

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Drupal\Core\Language\LanguageManagerInterface definition.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Drupal\mysql\Driver\Database\mysql\Connection definition.
   *
   * @var \Drupal\mysql\Driver\Database\mysql\Connection
   */
  protected $database;

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructor of Product controller.
   */
  public function __construct(ConfigFactoryInterface $configFactory, RequestStack $requestStack, Connection $database, LanguageManagerInterface $languageManager, ClientInterface $httpClient, LoggerChannelFactoryInterface $loggerFactory)
  {
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
    $this->loggerFactory = $loggerFactory;
    $this->languageManager = $languageManager;
    $this->database = $database;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    $httpClient = $container->get('http_client');
    $loggerFactory = $container->get('logger.factory');
    $languageManager = $container->get('language_manager');
    $database = $container->get('database');
    $requestStack = $container->get('request_stack');
    $configFactory = $container->get('config.factory');
    return new static($configFactory, $requestStack, $database, $languageManager, $httpClient, $loggerFactory);
  }

  /**
   * call validateShopcart() function.
   */
  public function validateShopcart()
  {
    $result = [];
    $result['statusCode'] = 2000;
    $output = '';
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);
    $shopcartid = isset($cartbody->shopcartId) ? $cartbody->shopcartId : '';
    $resp = json_decode(\Drupal::service('custom_example.cart')->validateCart($shopcartid, null, $language)->getContent());

    if ($resp->statusCode == '2000') {
      $result = $resp;
    } else {
      $finalshopcart = isset($resp->shopcart) ? $resp->shopcart : '';

      if (!empty($finalshopcart->Items)) {

        $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($finalshopcart->Items, $finalshopcart->ShopCartId, $language);

        // Separate arrays for isAddOn = 1 and isAddOn = 2
        $isAddOnArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 1;
        });

        $productArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 0;
        });

        // Sum of quantities for isAddOn = 1 and isAddOn = 2
        $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $sumProduct = array_reduce($productArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $build = [
          '#theme' => 'cart_lists',
          '#datas' => $result['data']['items'],
          '#language' => $language
        ];

        $output = \Drupal::service('renderer')->renderRoot($build);
        $result['data']['ShopCartId'] = isset($finalshopcart->ShopCartId) ? $finalshopcart->ShopCartId : '';
        $result['data']['totalAmount'] = isset($finalshopcart->TotalAmount) ? number_format($finalshopcart->TotalAmount, 2, '.', ',') : '';
        $result['data']['tabbyPrice'] = isset($finalshopcart->TotalAmount) ? $finalshopcart->TotalAmount / 4 : '0';
        $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
        $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
        $result['data']['totalCartQuantity'] = isset($finalshopcart->TotalQuantity) ? $finalshopcart->TotalQuantity : '0';
        $result['data']['totalTax'] = isset($finalshopcart->TotalTax) ? $finalshopcart->TotalTax : '';
        $result['data']['accountId'] = isset($finalshopcart->AccountId) ? $finalshopcart->AccountId : '';
        $result['data']['email'] = !empty($finalshopcart->ReceiptEmailAddress) && isset($finalshopcart->ReceiptEmailAddress) ? $finalshopcart->ReceiptEmailAddress : '';
        $result['data']['renderData'] = $output;
        $result['statusCode'] = 1000;
        $result['message'] = $this->t('Successfully added cart items!', [], ['langcode' => $language]);
      }
    }
    return new JsonResponse($result);
  }

  /**
   * Function addtoCart
   */
  public function addtoCart()
  {
    $result = [];
    $result['statusCode'] = 2000;
    $output = '';
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);
    $shopcartid = isset($cartbody->shopcartId) ? $cartbody->shopcartId : '';
    $productId = isset($cartbody->productId) ? $cartbody->productId : '';
    $quantity = isset($cartbody->quantity) ? $cartbody->quantity : '1';
    $start_date = isset($cartbody->start_date) ? $cartbody->start_date : null;
    $end_date = isset($cartbody->end_date) ? $cartbody->end_date : null;
    $perfomance_id = isset($cartbody->perfomance_id) ? $cartbody->perfomance_id : null;
    $existingProduct = false;

    if (!empty($shopcartid)) {
      $checkSpecialShopcartid = json_decode(\Drupal::service('custom_example.cart')->checkSpecialCharactor($shopcartid, $language)->getContent());
      if ($checkSpecialShopcartid->statusCode == 2000) {
        $response['statusCode'] = 2000;
        $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
        return new JsonResponse($response);
      }
    }

    if (!empty($productId)) {
      $checkSpecialProductId = json_decode(\Drupal::service('custom_example.cart')->checkSpecialCharactor($productId, $language)->getContent());
      if ($checkSpecialProductId->statusCode == 2000) {
        $response['statusCode'] = 2000;
        $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
        return new JsonResponse($response);
      }
    }

    if (!empty($perfomance_id)) {
      $checkSpecialPerfomanceId = json_decode(\Drupal::service('custom_example.cart')->checkSpecialCharactor($perfomance_id, $language)->getContent());
      if ($checkSpecialPerfomanceId->statusCode == 2000) {
        $response['statusCode'] = 2000;
        $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
        return new JsonResponse($response);
      }
    }

    if (!empty($quantity)) {
      $isValidDigits = json_decode(\Drupal::service('custom_example.cart')->isValidDigits($quantity, $language)->getContent());
      if ($isValidDigits->statusCode == 2000) {
        $response['statusCode'] = 2000;
        $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
        return new JsonResponse($response);
      }
    }


    if (!empty($shopcartid)) {
      $validateCart = json_decode(\Drupal::service('custom_example.cart')->validateCart($shopcartid, null, $language)->getContent());
      $validateCartItems = isset($validateCart->shopcart->Items) ? $validateCart->shopcart->Items : null;
      if (!empty($validateCartItems) && !empty($start_date) && !empty($end_date)) {
        $existingProduct = \Drupal::service('custom_example.cart')->differntDateCartCheck($validateCartItems, $start_date, $end_date);
      }
    }
    if (!empty($existingProduct)) {
      $result['statusCode'] = 3000;
      $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($validateCart->shopcart->Items, $validateCart->shopcart->ShopCartId, $language);

      // Separate arrays for isAddOn = 1 and isAddOn = 2
      $isAddOnArray = array_filter($result['data']['items'], function ($item) {
        return $item['isAddOn'] == 1;
      });

      $productArray = array_filter($result['data']['items'], function ($item) {
        return $item['isAddOn'] == 0;
      });

      // Sum of quantities for isAddOn = 1 and isAddOn = 2
      $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
        return $carry + $item['quantity'];
      }, 0);

      $sumProduct = array_reduce($productArray, function ($carry, $item) {
        return $carry + $item['quantity'];
      }, 0);

      $build = [
        '#theme' => 'cart_lists',
        '#datas' => $result['data']['items'],
        '#language' => $language
      ];
      $output = \Drupal::service('renderer')->renderRoot($build);

      $result['data']['ShopCartId'] = isset($validateCart->shopcart->ShopCartId) ? $validateCart->shopcart->ShopCartId : '';
      $result['data']['totalAmount'] = isset($validateCart->shopcart->TotalAmount) ? number_format($validateCart->shopcart->TotalAmount, 2, '.', ',') : '';
      $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
      $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
      $result['data']['totalCartQuantity'] = isset($validateCart->shopcart->TotalQuantity) ? $validateCart->shopcart->TotalQuantity : '0';
      $result['data']['totalTax'] = isset($validateCart->shopcart->TotalTax) ? $validateCart->shopcart->TotalTax : '';
      $result['data']['accountId'] = isset($validateCart->shopcart->AccountId) ? $validateCart->shopcart->AccountId : '';
      $result['data']['email'] = !empty($validateCart->shopcart->ReceiptEmailAddress) && isset($validateCart->shopcart->ReceiptEmailAddress) ? $validateCart->shopcart->ReceiptEmailAddress : '';
      $result['data']['renderData'] = $output;
      $result['message'] = $this->t('Please finalize your purchase with the currently selected date first!', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    $resp = json_decode(\Drupal::service('custom_example.cart')->addtocart($shopcartid, $productId, $quantity, $start_date, $end_date, $perfomance_id, $language)->getContent());
    if (isset($resp->shopcart->ShopCartId)) {
      $shopcartid = $resp->shopcart->ShopCartId;
    }
    if ($resp->statusCode == '2000') {
      $result = $resp;
    } else {
      $finalshopcart = $resp->shopcart;
      if (!empty($finalshopcart->Items)) {
        $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($finalshopcart->Items, $finalshopcart->ShopCartId, $language);

        // Separate arrays for isAddOn = 1 and isAddOn = 2
        $isAddOnArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 1;
        });

        $productArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 0;
        });

        // Sum of quantities for isAddOn = 1 and isAddOn = 2
        $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $sumProduct = array_reduce($productArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $build = [
          '#theme' => 'cart_lists',
          '#datas' => $result['data']['items'],
          '#language' => $language
        ];
        $output = \Drupal::service('renderer')->renderRoot($build);
      }
      $result['data']['ShopCartId'] = $finalshopcart->ShopCartId;
      $result['data']['totalAmount'] = isset($finalshopcart->TotalAmount) ? number_format($finalshopcart->TotalAmount, 2, '.', ',') : '';
      $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
      $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
      $result['data']['totalCartQuantity'] = isset($finalshopcart->TotalQuantity) ? $finalshopcart->TotalQuantity : '0';
      $result['data']['totalTax'] = isset($finalshopcart->TotalTax) ? $finalshopcart->TotalTax : '';
      $result['data']['renderData'] = $output;
      $result['statusCode'] = 1000;
      $result['message'] = $this->t('Successfully added cart items!', [], ['langcode' => $language]);
    }
    return new JsonResponse($result);
  }

  /**
   * Function annualPassAddtoCart 
   */
  public function annualPassAddtoCart()
  {
    $result = [];
    $result['statusCode'] = 2000;
    $output = '';
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);
    $shopcartid = isset($cartbody->shopcartId) ? $cartbody->shopcartId : '';
    $nid = isset($cartbody->nid) ? $cartbody->nid : '';
    $quantity = isset($cartbody->quantity) ? $cartbody->quantity : '1';
    $existingProduct = false;
    $node = !empty($nid) ? Node::load($nid) : null;
    $pNode = !empty($node) && !empty($node->field_content->target_id) ? Node::load($node->field_content->target_id) : null;

    $productId = !empty($pNode) ? $pNode->field_text_5->value : null;

    if (!empty($productId)) {
      $checkSpecialProductId = json_decode(\Drupal::service('custom_example.cart')->checkSpecialCharactor($productId, $language)->getContent());
      if ($checkSpecialProductId->statusCode == 2000) {
        $response['statusCode'] = 2000;
        $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
        return new JsonResponse($response);
      }
    }

    if (!empty($quantity)) {
      $isValidDigits = json_decode(\Drupal::service('custom_example.cart')->isValidDigits($quantity, $language)->getContent());
      if ($isValidDigits->statusCode == 2000) {
        $response['statusCode'] = 2000;
        $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
        return new JsonResponse($response);
      }
    }

    if (!empty($shopcartid)) {

      $checkSpecialShopcartid = json_decode(\Drupal::service('custom_example.cart')->checkSpecialCharactor($shopcartid, $language)->getContent());
      if ($checkSpecialShopcartid->statusCode == 2000) {
        $response['statusCode'] = 2000;
        $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
        return new JsonResponse($response);
      }

      $validateCart = json_decode(\Drupal::service('custom_example.cart')->validateCart($shopcartid, null, $language)->getContent());
      $validateCartItems = isset($validateCart->shopcart->Items) ? $validateCart->shopcart->Items : null;
      if (!empty($validateCartItems) && !empty($start_date) && !empty($end_date)) {
        $existingProduct = \Drupal::service('custom_example.cart')->differntDateCartCheck($validateCartItems, $start_date, $end_date);
      }
    }


    if (empty($productId)) {
      $result['message'] = $this->t('invalid product please contact administrator', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    if (!empty($shopcartid)) {
      $validateCart = json_decode(\Drupal::service('custom_example.cart')->validateCart($shopcartid, null, $language)->getContent());
      $validateCartItems = isset($validateCart->shopcart->Items) ? $validateCart->shopcart->Items : null;
      if (!empty($validateCartItems) && !empty($start_date) && !empty($end_date)) {
        $existingProduct = \Drupal::service('custom_example.cart')->differntDateCartCheck($validateCartItems, $start_date, $end_date);
      }
    }
    if (!empty($existingProduct)) {
      $result['statusCode'] = 3000;
      $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($validateCart->shopcart->Items, $validateCart->shopcart->ShopCartId, $language);

      // Separate arrays for isAddOn = 1 and isAddOn = 2
      $isAddOnArray = array_filter($result['data']['items'], function ($item) {
        return $item['isAddOn'] == 1;
      });

      $productArray = array_filter($result['data']['items'], function ($item) {
        return $item['isAddOn'] == 0;
      });

      // Sum of quantities for isAddOn = 1 and isAddOn = 2
      $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
        return $carry + $item['quantity'];
      }, 0);

      $sumProduct = array_reduce($productArray, function ($carry, $item) {
        return $carry + $item['quantity'];
      }, 0);

      $build = [
        '#theme' => 'cart_lists',
        '#datas' => $result['data']['items'],
        '#language' => $language
      ];
      $output = \Drupal::service('renderer')->renderRoot($build);

      $result['data']['ShopCartId'] = isset($validateCart->shopcart->ShopCartId) ? $validateCart->shopcart->ShopCartId : '';
      $result['data']['totalAmount'] = isset($validateCart->shopcart->TotalAmount) ? number_format($validateCart->shopcart->TotalAmount, 2, '.', ',') : '';
      $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
      $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
      $result['data']['totalCartQuantity'] = isset($validateCart->shopcart->TotalQuantity) ? $validateCart->shopcart->TotalQuantity : '0';
      $result['data']['totalTax'] = isset($validateCart->shopcart->TotalTax) ? $validateCart->shopcart->TotalTax : '';
      $result['data']['accountId'] = isset($validateCart->shopcart->AccountId) ? $validateCart->shopcart->AccountId : '';
      $result['data']['email'] = !empty($validateCart->shopcart->ReceiptEmailAddress) && isset($validateCart->shopcart->ReceiptEmailAddress) ? $validateCart->shopcart->ReceiptEmailAddress : '';
      $result['data']['renderData'] = $output;
      $result['message'] = $this->t('Please finalize your purchase with the currently selected date first!', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    $resp = json_decode(\Drupal::service('custom_example.cart')->addtocart($shopcartid, $productId, $quantity, null, null, null, $language)->getContent());
    if (isset($resp->shopcart->ShopCartId)) {
      $shopcartid = $resp->shopcart->ShopCartId;
    }
    if ($resp->statusCode == '2000') {
      $result = $resp;
    } else {
      $finalshopcart = $resp->shopcart;
      if (!empty($finalshopcart->Items)) {
        $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($finalshopcart->Items, $finalshopcart->ShopCartId, $language);

        // Separate arrays for isAddOn = 1 and isAddOn = 2
        $isAddOnArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 1;
        });

        $productArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 0;
        });

        // Sum of quantities for isAddOn = 1 and isAddOn = 2
        $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $sumProduct = array_reduce($productArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $build = [
          '#theme' => 'cart_lists',
          '#datas' => $result['data']['items'],
          '#language' => $language
        ];
        $output = \Drupal::service('renderer')->renderRoot($build);
      }
      $result['data']['ShopCartId'] = $finalshopcart->ShopCartId;
      $result['data']['totalAmount'] = isset($finalshopcart->TotalAmount) ? number_format($finalshopcart->TotalAmount, 2, '.', ',') : '';
      $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
      $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
      $result['data']['totalCartQuantity'] = isset($finalshopcart->TotalQuantity) ? $finalshopcart->TotalQuantity : '0';
      $result['data']['totalTax'] = isset($finalshopcart->TotalTax) ? $finalshopcart->TotalTax : '';
      $result['data']['renderData'] = $output;
      $result['statusCode'] = 1000;
      $result['message'] = $this->t('Successfully added cart items!', [], ['langcode' => $language]);
    }
    return new JsonResponse($result);
  }

  /**
   * Function editItem page
   */
  public function editItem()
  {
    $result = [];
    $result['statusCode'] = 2000;
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);
    $shopcartid = isset($cartbody->shopcartId) ? $cartbody->shopcartId : '';
    $shopcartItemid = isset($cartbody->shopcartItemId) ? $cartbody->shopcartItemId : '';
    $quantity = isset($cartbody->quantity) ? $cartbody->quantity : '';
    $quantityInput = isset($cartbody->quantityInput) ? $cartbody->quantityInput : '';
    $output = $annualpass_form_output = '';

    $checkSpecialShopcartid = json_decode(\Drupal::service('custom_example.cart')->checkSpecialCharactor($shopcartid, $language)->getContent());
    if ($checkSpecialShopcartid->statusCode == 2000) {
      $response['statusCode'] = 2000;
      $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
      return new JsonResponse($response);
    }

    if (!empty($quantityInput) && $quantityInput > 10) {
      $response['statusCode'] = 2000;
      $response['message'] =  $this->t('You are not allowed to purchase more than 10 of this item.', [], ['langcode' => $language]);
      return new JsonResponse($response);
    }

    $checkSpecialShopcartItemId = json_decode(\Drupal::service('custom_example.cart')->checkSpecialCharactor($shopcartItemid, $language)->getContent());
    if ($checkSpecialShopcartItemId->statusCode == 2000) {
      $response['statusCode'] = 2000;
      $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
      return new JsonResponse($response);
    }

    $isValidString = json_decode(\Drupal::service('custom_example.cart')->isValidString($quantity, $language)->getContent());
    if ($isValidString->statusCode == 2000) {
      $response['statusCode'] = 2000;
      $response['message'] =  $this->t('Invalid request', [], ['langcode' => $language]);
      return new JsonResponse($response);
    }


    $resp = json_decode(\Drupal::service('custom_example.cart')->editQuantiy($shopcartid, $shopcartItemid, $quantity, NULL, FALSE, $language)->getContent());
    if ($resp->statusCode == '2000') {
      $result = $resp;
    } else {
      $finalshopcart = $resp->shopcart;
      if (!empty($finalshopcart->Items)) {
        $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($finalshopcart->Items, $finalshopcart->ShopCartId, $language);

        // Separate arrays for isAddOn = 1 and isAddOn = 2
        $isAddOnArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 1;
        });

        $productArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 0;
        });

        $isAnnualPassCount = count(array_filter($result['data']['items'], function ($item) {
          return $item["isAnnualPass"] == 1;
        }));

        if (!empty($isAnnualPassCount)) {
          // Webform Options
          $titles = WebformOptions::load('titles');
          $titleOptions = $titles->getOptions() ?: [];
          $formOptions['titleOptions'] = $titleOptions;

          $countryLists = WebformOptions::load('country_lists');
          $countryListsOptions = $countryLists->getOptions() ?: [];
          $formOptions['countryListsOptions'] = $countryListsOptions;

          $countryPhoneCodes = WebformOptions::load('country_phone_code');
          $countryPhoneCodesOptions = $countryPhoneCodes->getOptions() ?: [];
          $formOptions['countryPhoneCodesOptions'] = $countryPhoneCodesOptions;
          // End webform options
          $annualPassProducts = \Drupal::service('custom_example.product')->getAnnualPassProducts($result['data']['items']);
          $annualpass_form = [
            '#theme' => 'annualpass_form',
            '#formOptions' => $formOptions,
            '#annualPassProducts' => $annualPassProducts,
          ];
          $annualpass_form_output = \Drupal::service('renderer')->renderRoot($annualpass_form);
        }

        // Sum of quantities for isAddOn = 1 and isAddOn = 2
        $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $sumProduct = array_reduce($productArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $build = [
          '#theme' => 'cart_lists',
          '#datas' => $result['data']['items'],
          '#language' => $language
        ];
        $output = \Drupal::service('renderer')->renderRoot($build);
        $result['data']['ShopCartId'] = $finalshopcart->ShopCartId;
        $result['data']['totalAmount'] = isset($finalshopcart->TotalAmount) ? number_format($finalshopcart->TotalAmount, 2, '.', ',') : '';
        $result['data']['totalTax'] = isset($finalshopcart->TotalTax) ? $finalshopcart->TotalTax : '';
        $result['data']['tabbyPrice'] = isset($finalshopcart->TotalAmount) ? $finalshopcart->TotalAmount / 4 : '0';
        $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
        $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
        $result['data']['totalCartQuantity'] = isset($finalshopcart->TotalQuantity) ? $finalshopcart->TotalQuantity : '0';
        $result['data']['annualPassFrom'] = $annualpass_form_output;
        $result['data']['renderData'] = $output;
        $result['statusCode'] = 1000;
        $result['message'] = $this->t('Successfully edited cart items!', [], ['langcode' => $language]);
      }
    }
    return new JsonResponse($result);
  }

  /**
   * Function removeShopcartItem
   */
  public function removeShopcartItem()
  {
    $result = [];
    $result['statusCode'] = 2000;
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);
    $shopcartid = isset($cartbody->shopcartId) ? $cartbody->shopcartId : '';
    $shopcartItemid = isset($cartbody->shopcartItemId) ? $cartbody->shopcartItemId : '';
    $quantity = isset($cartbody->quantity) ? $cartbody->quantity : '';
    $resp = json_decode(\Drupal::service('custom_example.cart')->removeShopcartItem($shopcartid, $shopcartItemid, $quantity, NULL, FALSE, $language)->getContent());

    $output = $booking_summaries = $annualpass_form_output = '';
    if ($resp->statusCode == '2000') {
      $result = $resp;
    } else {
      $finalshopcart = $resp->shopcart;
      // if (!empty($finalshopcart->Items)) {
      $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($finalshopcart->Items, $finalshopcart->ShopCartId, $language);

      // Separate arrays for isAddOn = 1 and isAddOn = 2
      $isAddOnArray = array_filter($result['data']['items'], function ($item) {
        return $item['isAddOn'] == 1;
      });

      $productArray = array_filter($result['data']['items'], function ($item) {
        return $item['isAddOn'] == 0;
      });

      $isAnnualPassCount = count(array_filter($result['data']['items'], function ($item) {
        return $item["isAnnualPass"] == 1;
      }));

      if (!empty($isAnnualPassCount)) {
        // Webform Options
        $titles = WebformOptions::load('titles');
        $titleOptions = $titles->getOptions() ?: [];
        $formOptions['titleOptions'] = $titleOptions;

        $countryLists = WebformOptions::load('country_lists');
        $countryListsOptions = $countryLists->getOptions() ?: [];
        $formOptions['countryListsOptions'] = $countryListsOptions;

        $countryPhoneCodes = WebformOptions::load('country_phone_code');
        $countryPhoneCodesOptions = $countryPhoneCodes->getOptions() ?: [];
        $formOptions['countryPhoneCodesOptions'] = $countryPhoneCodesOptions;
        // End webform options
        $annualPassProducts = \Drupal::service('custom_example.product')->getAnnualPassProducts($result['data']['items']);
        $annualpass_form = [
          '#theme' => 'annualpass_form',
          '#formOptions' => $formOptions,
          '#annualPassProducts' => $annualPassProducts,
        ];
        $annualpass_form_output = \Drupal::service('renderer')->renderRoot($annualpass_form);
      }

      // Sum of quantities for isAddOn = 1 and isAddOn = 2
      $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
        return $carry + $item['quantity'];
      }, 0);

      $sumProduct = array_reduce($productArray, function ($carry, $item) {
        return $carry + $item['quantity'];
      }, 0);

      $build = [
        '#theme' => 'cart_lists',
        '#datas' => $result['data']['items'],
        '#language' => $language
      ];
      $output = \Drupal::service('renderer')->renderRoot($build);

      $booking_summaries = [
        '#theme' => 'booking_summaries',
        '#items' => $result['data']['items'],
      ];
      $booking_summaries_output = \Drupal::service('renderer')->renderRoot($booking_summaries);
      $result['data']['ShopCartId'] = $finalshopcart->ShopCartId;
      $result['data']['totalAmount'] = isset($finalshopcart->TotalAmount) ? number_format($finalshopcart->TotalAmount, 2, '.', ',') : '';
      $result['data']['tabbyPrice'] = isset($finalshopcart->TotalAmount) ? $finalshopcart->TotalAmount / 4 : '0';
      $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
      $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
      $result['data']['totalCartQuantity'] = isset($finalshopcart->TotalQuantity) ? $finalshopcart->TotalQuantity : '0';
      $result['data']['totalTax'] = isset($finalshopcart->TotalTax) ? $finalshopcart->TotalTax : '';
      $result['data']['renderData'] = $output;
      $result['data']['bookingSummary'] = $booking_summaries_output;
      $result['data']['annualPassFrom'] = $annualpass_form_output;
      $result['statusCode'] = 1000;
      $result['message'] = $this->t('Successfully removed item!', [], ['langcode' => $language]);
      // }
    }
    return new JsonResponse($result);
  }

  /**
   * Function empty cart
   */
  public function emptyCart()
  {
    $result = [];
    $result['statusCode'] = 2000;
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);
    $shopcartid = isset($cartbody->shopcartId) ? $cartbody->shopcartId : '';
    $resp = json_decode(\Drupal::service('custom_example.cart')->emptyCart($shopcartid, NULL, FALSE, $language)->getContent());
    $output = '';
    if ($resp->statusCode == '2000') {
      $result = $resp;
    } else {
      $finalshopcart = $resp->shopcart;
      if (!empty($finalshopcart->Items)) {
        $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($finalshopcart->Items, $finalshopcart->ShopCartId, $language);

        // Separate arrays for isAddOn = 1 and isAddOn = 2
        $isAddOnArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 1;
        });

        $productArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 0;
        });

        // Sum of quantities for isAddOn = 1 and isAddOn = 2
        $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $sumProduct = array_reduce($productArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $build = [
          '#theme' => 'cart_lists',
          '#datas' => $result['data']['items'],
          '#language' => $language
        ];
        $output = \Drupal::service('renderer')->renderRoot($build);
        $result['data']['ShopCartId'] = $finalshopcart->ShopCartId;
        $result['data']['totalAmount'] = isset($finalshopcart->TotalAmount) ? number_format($finalshopcart->TotalAmount, 2, '.', ',') : '';
        $result['data']['totalTax'] = isset($finalshopcart->TotalTax) ? $finalshopcart->TotalTax : '';
        $result['data']['tabbyPrice'] = isset($finalshopcart->TotalAmount) ? $finalshopcart->TotalAmount / 4 : '0';
        $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
        $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
        $result['data']['totalCartQuantity'] = isset($finalshopcart->TotalQuantity) ? $finalshopcart->TotalQuantity : '0';
        $result['data']['renderData'] = $output;
        $result['statusCode'] = 1000;
        $result['message'] = $this->t('Successfully removed cart!', [], ['langcode' => $language]);
      }
    }
    return new JsonResponse($result);
  }

  /**
   * Function cart checkout
   */
  public function cartCheckout()
  {
    $language = $this->languageManager->getCurrentLanguage()->getId();
    // Your custom page logic goes here.
    $query = $this->database->select('dependent_country', 'dc')
      ->condition('dc.status', 1)
      ->orderby('dc.country_name', 'asc');
    $query->fields('dc', []);
    $result = $query->execute();
    $country_obj = $result->fetchAll();
    $uid = \Drupal::currentUser()->id();
    // Create a response object.
    $request = \Drupal::request();
    // Remove the cookie by setting an expired date.
    $shopcartid = $request->cookies->get('getcustomShopcartId');
    $nodeData = [];
    $resp = json_decode(\Drupal::service('custom_example.cart')->validateCart($shopcartid, null, $language)->getContent());
    $i = 0;
    if (isset($resp->statusCode) && $resp->statusCode == 1000) {
      if (isset($resp->shopcart->Items)) {
        foreach ($resp->shopcart->Items as $key => $item) {
          $anode = \Drupal::service('custom_example.product')->getProductWithProductId($item->ProductId, $language);
          if (!empty($anode)) {
            $nodeData[$i] = $anode;
            $nodeData[$i]['shopCartItemId'] = $item->ShopCartItemId;
            $nodeData[$i]['totalAmount'] = $item->TotalAmount;
            $nodeData[$i]['totalQuantity'] = $item->Quantity;
            $i++;
          }
        }
      }
    }
    dd($nodeData);
    $userDetails = '';
    if (!empty($uid)) {
      $userDetails = (object)\Drupal::service('custom_example.account')->getUser($uid);
    }
    $build['content'] = array(
      '#type' => 'markup',
      '#theme' => 'cart_checkout',
      '#countries' => $country_obj,
      '#userDetails' => $userDetails,
      '#annualPass' => $nodeData
    );
    $build['#cache']['max-age'] = 0;
    return $build;
  }

  /**
   * Function create a cart user
   */
  public function createCartUser()
  {
    $result = [];
    $result['statusCode'] = 2000;
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);

    $shopcartId = !empty($cartbody->shopcartId) ? $cartbody->shopcartId : null;
    $title = !empty($cartbody->title) ? $cartbody->title : null;
    $firstname = !empty($cartbody->firstname) ? $cartbody->firstname : null;
    $lastname = !empty($cartbody->lastname) ? $cartbody->lastname : null;
    $email = !empty($cartbody->email) ? $cartbody->email : null;
    $confirm_email = !empty($cartbody->confirm_email) ? $cartbody->confirm_email : null;
    $phonecode = !empty($cartbody->phonecode) ? $cartbody->phonecode : null;
    $phonenumber = !empty($cartbody->phonenumber) ? $cartbody->phonenumber : null;
    $country = !empty($cartbody->country) ? $cartbody->country : null;
    $city = !empty($cartbody->city) ? $cartbody->city : null;
    $nationality = !empty($cartbody->nationality) ? $cartbody->nationality : null;
    $accept_terms = !empty($cartbody->accept_terms) ? $cartbody->accept_terms : 0;
    $uid = \Drupal::currentUser()->id();
    if (empty($shopcartId)) {
      $result['message'] = $this->t('Invalid shopcart item!', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    if (empty($uid) && $uid != 0) {
      if (empty($title)) {
        $result['message'] = $this->t('Please choose title!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($firstname)) {
        $result['message'] = $this->t('Please enter firstname!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($lastname)) {
        $result['message'] = $this->t('Please enter lastname!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($email)) {
        $result['message'] = $this->t('Please enter email!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
        $result['message'] = $this->t('Please enter valid email id', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($confirm_email) && $email != $confirm_email) {
        $result['message'] = $this->t('Please enter valid confirm email!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($phonecode)) {
        $result['message'] = $this->t('Please choose mobile country code!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($phonenumber)) {
        $result['message'] = $this->t('Please enter mobile number!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($country)) {
        $result['message'] = $this->t('Please choose valid country!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($city)) {
        $result['message'] = $this->t('Please enter city!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($nationality)) {
        $result['message'] = $this->t('Please choose valid nationality!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }

      if (empty($accept_terms)) {
        $result['message'] = $this->t('Please accept terms and conditions!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }
    } else {
      $userDetails = (object)\Drupal::service('custom_example.account')->getUser($uid);
      $firstname = !empty($userDetails->firstname) ? $userDetails->firstname : null;
      $lastname = !empty($userDetails->lastname) ? $userDetails->lastname : null;
      $email = !empty($userDetails->email) ? $userDetails->email : null;
      $phonecode = !empty($userDetails->phonecode) ? $userDetails->phonecode : null;
      $phonenumber = !empty($userDetails->phonenumber) ? $userDetails->phonenumber : null;
      $country = !empty($userDetails->country) ? $userDetails->country : null;
      $city = !empty($userDetails->city) ? $userDetails->city : null;
      $nationality = !empty($userDetails->nationality) ? $cartbody->nationality : null;
    }


    $account = json_decode(\Drupal::service('custom_example.account')->searchAccount($email, $language)->getContent());
    $accountId = !empty($account->AccountId) ? $account->AccountId : null;
    if (empty($account) && empty($account->AccountId)) {
      $user = (object) [
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $email,
        'phonenumber' => $phonenumber,
        'country' => $country,
        'nationality' => $nationality,
        'phonenumber' => $phonenumber
      ];
      $account = json_decode(\Drupal::service('custom_example.account')->saveAccount2($user, $language)->getContent());
      $accountId = !empty($account->AccountId) ? $account->AccountId : null;
    }
    $setAccount = json_decode(\Drupal::service('custom_example.account')->setOwnerAccount($shopcartId, $accountId, NULL, $language)->getContent());
    if (!empty($setAccount) && $setAccount->statusCode == 1000) {
      $result['statusCode'] = 1000;
      $result['data'] = isset($setAccount->shopcart) ? $setAccount->shopcart : null;
      $result['message'] = $setAccount->message;
    }
    return new JsonResponse($result);
  }

  /**
   * Function apply coupon
   */
  public function applyCoupon()
  {
    $result = [];
    $result['statusCode'] = 2000;
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);
    $shopcartid = isset($cartbody->shopcartId) ? $cartbody->shopcartId : '';
    $couponCode = isset($cartbody->couponCode) ? $cartbody->couponCode : '';

    if (empty($couponCode)) {
      $result['message'] = $this->t('Please enter valid Coupon!', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    if (empty($shopcartid)) {
      $result['message'] = $this->t('Invalid shopcart!', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }
    $booking_summaries_output = $output = '';
    $resp = json_decode(\Drupal::service('custom_example.cart')->applyCoupon($couponCode, $shopcartid, NULL, $language)->getContent());
    if ($resp->statusCode == '2000') {
      $result = $resp;
    } else {
      $finalshopcart = $resp->shopcart;
      if (!empty($finalshopcart->Items)) {
        $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($finalshopcart->Items, $finalshopcart->ShopCartId, $language);

        // Separate arrays for isAddOn = 1 and isAddOn = 2
        $isAddOnArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 1;
        });

        $productArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 0;
        });

        // Sum of quantities for isAddOn = 1 and isAddOn = 2
        $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $sumProduct = array_reduce($productArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $build = [
          '#theme' => 'cart_lists',
          '#datas' => $result['data']['items'],
          '#language' => $language
        ];
        $output = \Drupal::service('renderer')->renderRoot($build);

        $booking_summaries = [
          '#theme' => 'booking_summaries',
          '#items' => $result['data']['items'],
        ];
        $booking_summaries_output = \Drupal::service('renderer')->renderRoot($booking_summaries);

        $result['data']['ShopCartId'] = $finalshopcart->ShopCartId;
        $result['data']['totalAmount'] = isset($finalshopcart->TotalAmount) ? number_format($finalshopcart->TotalAmount, 2, '.', ',') : '';
        $result['data']['totalTax'] = isset($finalshopcart->TotalTax) ? $finalshopcart->TotalTax : '';
        $result['data']['tabbyPrice'] = isset($finalshopcart->TotalAmount) ? $finalshopcart->TotalAmount / 4 : '0';
        $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
        $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
        $result['data']['totalCartQuantity'] = isset($finalshopcart->TotalQuantity) ? $finalshopcart->TotalQuantity : '0';
        $result['data']['renderData'] = $output;
        $result['data']['bookingSummary'] = $booking_summaries_output;
        $result['statusCode'] = 1000;
        $result['message'] = $this->t('Coupon applied to cart successfully.!', [], ['langcode' => $language]);
      }
    }
    return new JsonResponse($result);
  }

  /**
   * Function guest Checkout
   */
  public function guestexampleCheckout()
  {
    $result['statusCode'] = 2000;
    $base_url = \Drupal::request()->getSchemeAndHttpHost();
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);
    $firstName = isset($cartbody->firstName) ? $cartbody->firstName : '';
    $lastName = isset($cartbody->lastName) ? $cartbody->lastName : '';
    $email = isset($cartbody->email) ? $cartbody->email : '';
    $term_check = isset($cartbody->term_check) ? $cartbody->term_check : '';
    $guest_gift_check = isset($cartbody->guest_gift_check) ? $cartbody->guest_gift_check : '';
    $gift_email = isset($cartbody->gift_email) ? $cartbody->gift_email : '';
    $confirm_gift_email = isset($cartbody->confirm_gift_email) ? $cartbody->confirm_gift_email : '';
    $annualpassCheck = isset($cartbody->annualpassCheck) ? $cartbody->annualpassCheck : '';

    $shopcartId = $request->cookies->get('getcustomShopcartId');
    $giftEnabeld = 0;

    if (empty($firstName)) {
      $result['message'] = $this->t('Please enter first name', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    if (empty($lastName)) {
      $result['message'] = $this->t('Please enter last name', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    if (empty($email)) {
      $result['message'] = $this->t('Please enter valid email!', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    if ($guest_gift_check == 'checked') {
      $giftEnabeld = 1;
      if (empty($gift_email)) {
        $result['message'] = $this->t('Please enter gift user valid email!', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }
      if ($confirm_gift_email != $gift_email) {
        $result['message'] = $this->t('Your email and confirm email do not match', [], ['langcode' => $language]);
        return new JsonResponse($result);
      }
    }

    // if (empty($term_check)) {
    //   $result['message'] = $this->t('Please accept Terms & Conditions', [], ['langcode' => $language]);
    //   return new JsonResponse($result);
    // }

    $resp = json_decode(\Drupal::service('custom_example.cart')->validateCart($shopcartId, null, $language)->getContent());
    if (isset($resp->shopcart->Items) && count($resp->shopcart->Items) == 0) {
      $result['message'] = $this->t('Shopcart transaction invalid. Please try again.', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    if (!empty($resp) && !empty($resp->shopcart)) {
      $account = json_decode(\Drupal::service('custom_example.account')->searchAccount($email, $language)->getContent());
      $accountId = !empty($account->AccountId) ? $account->AccountId : null;
      if (empty($accountId)) {
        $user = (object) [
          'firstname' => $firstName,
          'lastname' => $lastName,
          'email' => $email
        ];
        $account = json_decode(\Drupal::service('custom_example.account')->saveAccount2($user, $language)->getContent());
        $accountId = !empty($account->AccountId) ? $account->AccountId : null;
      }
      $setAccount = json_decode(\Drupal::service('custom_example.account')->setOwnerAccount($shopcartId, $accountId, NULL, $language)->getContent());
      if (!empty($giftEnabeld) && $giftEnabeld == 1) {

        $searchAccount = json_decode(\Drupal::service('custom_example.account')->searchAccount($gift_email, $language)->getContent());
        if ($searchAccount->statusCode == '1000') {
          $sAccountId = !empty($searchAccount->AccountId) ? $searchAccount->AccountId : null;
        }
        if ($searchAccount->statusCode == '2000') {
          $user = (object) [
            'email' => $gift_email,
            'firstname' => $firstName,
            'lastname' => $lastName,

          ];
          $saveAccount = json_decode(\Drupal::service('custom_example.account')->saveAccount2GiftUser($user, $language)->getContent());
          $sAccountId = !empty($saveAccount->AccountId) ? $saveAccount->AccountId : null;
        }
        if (!empty($accountId)) {
          $setShipAccount = json_decode(\Drupal::service('custom_example.cart')->addToCartSetShipAccount($shopcartId, $sAccountId, NULL, $language)->getContent());
        }
      }

      if (!empty($annualpassCheck[0])) {
        $accountInformations = [];
        foreach ($annualpassCheck as $key => $annualpass) {
          $title = $annualpass->title;
          $firstname = $annualpass->firstname;
          $lastname = $annualpass->lastname;
          $annulapass_email = $annualpass->annulapass_email;
          $dateofbirth = $annualpass->dateofbirth;
          $nationality = $annualpass->nationality;
          $country_code = $annualpass->country_code;
          $phonenumber = $annualpass->phonenumber;
          $country = $annualpass->country;
          $region = $annualpass->region;

          if (empty($title)) {
            $result['message'] = $this->t('Please enter annual pass user title', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }

          if (empty($firstname)) {
            $result['message'] = $this->t('Please enter annual pass user first name', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }

          if (empty($lastname)) {
            $result['message'] = $this->t('Please enter annual pass user last name', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }

          if (empty($annulapass_email)) {
            $result['message'] = $this->t('Please enter annual pass user email', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }
          if (!filter_var($annulapass_email, FILTER_VALIDATE_EMAIL)) {
            $result['message'] = $this->t('Please enter annual pass user valid email', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }
          if (empty($dateofbirth)) {
            $result['message'] = $this->t('Please enter annual pass user date of birth', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }

          // if (empty($nationality)) {
          //   $result['message'] = $this->t('Please enter annual pass user nationality', [], ['langcode' => $language]);
          //   return new JsonResponse($result);
          // }

          if (empty($country_code)) {
            $result['message'] = $this->t('Please enter annual pass user phone code', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }

          if (empty($phonenumber)) {
            $result['message'] = $this->t('Please enter annual pass user phone number', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }

          if (empty($country)) {
            $result['message'] = $this->t('Please enter annual pass user country', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }

          if (empty($region)) {
            $result['message'] = $this->t('Please enter annual pass user region', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }


          $annualPassBuyFlow = $this->annualPassBuyFlow($annualpass);
          if (isset($annualPassBuyFlow->statusCode) && $annualPassBuyFlow->statusCode == 1000 && isset($annualPassBuyFlow->AccountId)) {
            $accountInformations[$key]['accountId'] = $annualPassBuyFlow->AccountId;
            $accountInformations[$key]['shopcartItemId'] = $annualPassBuyFlow->shopcartItemId;
          }
        }
        if (!empty($accountInformations)) {
          $setItemDetail = json_decode(\Drupal::service('custom_example.cart')->setItemDetails($shopcartId, $accountInformations, $language)->getContent());
          if (isset($setItemDetail->statusCode) && $setItemDetail->statusCode == 2000) {
            $result['message'] = $this->t('Oops, something went wrong. Please try again.', [], ['langcode' => $language]);
            return new JsonResponse($result);
          }
        }
      }

      $finalShopcart = isset($setAccount->shopcart) ? $setAccount->shopcart : null;
      if (isset($setAccount->statusCode) && $setAccount->statusCode == 1000) {
        $result['data']['ShopCartId'] = $finalShopcart->ShopCartId;
        $result['data']['totalAmount'] = isset($finalShopcart->TotalAmount) ? number_format($finalShopcart->TotalAmount, 2, '.', ',') : '';
        $result['data']['totalTax'] = isset($finalShopcart->TotalTax) ? $finalShopcart->TotalTax : '';
        $result['data']['totalCartQuantity'] = isset($finalShopcart->TotalQuantity) ? $finalShopcart->TotalQuantity : '0';
        $result['statusCode'] = 1000;
      }
    }
    return new JsonResponse($result);
  }

  /**
   * Function guest Checkout
   */
  public function guestexampleCheckoutV1()
  {
    $result = [];
    $result['statusCode'] = 2000;
    $request = $this->requestStack->getCurrentRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $cartbody = (string) $request->getContent();
    $cartbody = json_decode($cartbody);
    $shopcartid = isset($cartbody->shopcartId) ? $cartbody->shopcartId : '';
    $email = isset($cartbody->email) ? $cartbody->email : '';
    $output = $booking_summaries_output = '';

    if (empty($email)) {
      $result['message'] = $this->t('Please enter valid email!', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    $validateEmail = \Drupal::service('custom_example.account')->isValidEmail($email);
    if (empty($validateEmail)) {
      $result['message'] = $this->t('Please enter valid email!', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }

    if (empty($shopcartid)) {
      $result['message'] = $this->t('Invalid shopcart!', [], ['langcode' => $language]);
      return new JsonResponse($result);
    }
    $searchAccount = json_decode(\Drupal::service('custom_example.account')->searchAccount($email, $language)->getContent());
    if ($searchAccount->statusCode == '1000') {
      $accountId = !empty($searchAccount->AccountId) ? $searchAccount->AccountId : null;
    }
    if ($searchAccount->statusCode == '2000') {
      $user = (object) [
        'email' => $email
      ];
      $saveAccount = json_decode(\Drupal::service('custom_example.account')->saveAccount2($user, $language)->getContent());
      $accountId = !empty($saveAccount->AccountId) ? $saveAccount->AccountId : null;
    }
    if (!empty($accountId)) {
      $setAccount = json_decode(\Drupal::service('custom_example.account')->setOwnerAccount($shopcartid, $accountId, NULL, $language)->getContent());
      if (!empty($setAccount) && $setAccount->statusCode == 1000) {
        $result['data']['items'] = \Drupal::service('custom_example.cart')->getShopCartItemsList($setAccount->shopcart->Items, $shopcartid, $language);

        // Separate arrays for isAddOn = 1 and isAddOn = 2
        $isAddOnArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 1;
        });

        $productArray = array_filter($result['data']['items'], function ($item) {
          return $item['isAddOn'] == 0;
        });

        // Sum of quantities for isAddOn = 1 and isAddOn = 2
        $sumAddOn = array_reduce($isAddOnArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $sumProduct = array_reduce($productArray, function ($carry, $item) {
          return $carry + $item['quantity'];
        }, 0);

        $build = [
          '#theme' => 'cart_lists',
          '#datas' => $result['data']['items'],
          '#language' => $language
        ];
        $output = \Drupal::service('renderer')->renderRoot($build);

        $booking_summaries = [
          '#theme' => 'booking_summaries',
          '#items' => $result['data']['items'],
        ];
        $booking_summaries_output = \Drupal::service('renderer')->renderRoot($booking_summaries);

        $result['statusCode'] = 1000;

        // $result['data'] = isset($setAccount->shopcart) ? $setAccount->shopcart : null;
        $result['data']['ShopCartId'] = isset($setAccount->shopcart->ShopCartId) ? $setAccount->shopcart->ShopCartId : '';
        $result['data']['totalAmount'] = !empty($setAccount->shopcart->TotalAmount) && isset($setAccount->shopcart->TotalAmount) ? number_format($setAccount->shopcart->TotalAmount, 2, '.', ',') : '';
        $result['data']['totalTax'] = isset($setAccount->shopcart->TotalTax) ? $setAccount->shopcart->TotalTax : '';
        $result['data']['accountId'] = isset($setAccount->shopcart->AccountId) ? $setAccount->shopcart->AccountId : '';
        $result['data']['email'] = !empty($setAccount->shopcart->ReceiptEmailAddress) && isset($setAccount->shopcart->ReceiptEmailAddress) ? $setAccount->shopcart->ReceiptEmailAddress : '';
        $result['data']['totalQuantity'] = !empty($sumProduct) ? $sumProduct : '0';
        $result['data']['totalAddOnQuantity'] = !empty($sumAddOn) ? $sumAddOn : '0';
        $result['data']['totalCartQuantity'] = isset($setAccount->shopcart->TotalQuantity) ? $setAccount->shopcart->TotalQuantity : '0';
        $result['data']['renderData'] = $output;
        $result['data']['bookingSummary'] = $booking_summaries_output;
        $result['message'] = $setAccount->message;
      }
    }
    return new JsonResponse($result);
  }
}
