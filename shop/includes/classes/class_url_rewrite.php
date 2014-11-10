<?php
/* ----------------------------------------------------------------------
   $Id: class_url_rewrite.php,v 1.1 2007/06/07 16:06:31 r23 Exp $

   OOS [OSIS Online Shop]
   http://www.oos-shop.de/

   Copyright (c) 2003 - 2007 by the OOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   ----------------------------------------------------------------------
   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2003 osCommerce
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

  /** ensure this file is being included by a parent file */
  defined( 'OOS_VALID_MOD' ) or die( 'Direct Access to this location is not allowed.' );

  class url_rewrite{


    function transform_uri($param) {

      unset($path);
      unset($url);

      $uri = explode("index.php/", $param);

      $path = $uri[1];
      $base = $uri[0];

      $url_array = explode('/', $path);

      $aFilename = oos_get_filename();
      $aModules = oos_get_modules();

      if ( (in_array('cPath', $url_array)) || (in_array($aFilename['product_info'], $url_array) && in_array($aModules['products'], $url_array)) ) {

        $_filter = array('mp', 'file', $aModules['main'], $aModules['products'], $aFilename['shop'], oos_session_name(), oos_session_id());

        $dbconn =& oosDBGetConn();
        $oostable =& oosDBGetTables();

        $nLanguageID = isset($_SESSION['language_id']) ? $_SESSION['language_id']+0 : 1;

        $path = '';
        $extention = '.html';

        for ($i=0; $i < count($url_array); $i++){
          switch ($url_array[$i]) {
            case 'cPath':
              unset($category);
              $category = '';
              $i++;
              if(eregi('[_0-9]', $url_array[$i])){
                if($category_array = explode('_', $url_array[$i])){
                  foreach($category_array as $value){
                    $categoriestable = $oostable['categories'];
                    $categories_descriptiontable = $oostable['categories_description'];
                    $category_result = $dbconn->Execute("SELECT c.categories_id, cd.categories_name FROM  $categoriestable c, $categories_descriptiontable cd WHERE c.categories_id = '" . intval($value) . "' AND c.categories_id = cd.categories_id AND cd.categories_languages_id = '" . intval($nLanguageID) . "'");
                    $category .= oos_make_filename($category_result->fields['categories_name']) . '/';
                  }
                  $category = substr($category, 0, -1);
                  $category .= '-c-' .  $url_array[$i]. '/';
                } else {
                  $category .= 'cPath/' . $url_array[$i] . '/';
                }
              }
              $path .= $category;
              break;

            case 'products_id':
              unset($product);
              $i++;
              if ($url_array[$i]) {
                $products_descriptiontable = $oostable['products_description'];
                $product_result = $dbconn->Execute("SELECT products_name FROM $products_descriptiontable WHERE products_id = '" . intval($url_array[$i]) . "' AND products_languages_id = '" .  intval($nLanguageID) . "'");
                $product = oos_make_filename($product_result->fields['products_name']);
                $path .= $product . '-p-' . $url_array[$i] . '/';
              }
              break;

            case 'manufacturers_id':
              unset($manufacturer);
              $i++;
              if ($url_array[$i]) {
                $manufacturerstable = $oostable['manufacturers'];
                $manufacturer_result = $dbconn->Execute("SELECT manufacturers_name FROM $manufacturerstable WHERE manufacturers_id = '" . intval($url_array[$i]) . "'");
                $manufacturer = oos_make_filename($manufacturer_result->fields['manufacturers_name']);
                $path .= $manufacturer . '-m-' . $url_array[$i] . '/';
              }
              break;

            default:
              if (!in_array($url_array[$i], $_filter)) {
                $path .= $url_array[$i] . '/';
              }
              break;
          }
        }

        $pos = strpos ($path, "-p-");
        if ($pos === false) {
          // $remove = array('-c-');
        } else {
          $remove = array('-m-', '-c-');
        }
        $path = str_replace($remove, '', $path);
        if (strpos($path, '//') !== false) $path = str_replace('//', '/', $path);
        if (substr($path, -1) == '/') $path = substr($path, 0, -1);

        $url = $base . $path . $extention;
      } else {
        $url = $param;
      }

      return $url;
    }
  }

?>