<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tracker;

use Exception;
use Piwik\Common;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Tracker;

/**
 */
class GoalManager
{
    // log_visit.visit_goal_buyer
    const TYPE_BUYER_OPEN_CART = 2;
    const TYPE_BUYER_ORDERED_AND_OPEN_CART = 3;

    // log_conversion.idorder is NULLable, but not log_conversion_item which defaults to zero for carts
    const ITEM_IDORDER_ABANDONED_CART = 0;

    // log_conversion.idgoal special values
    const IDGOAL_CART = -1;
    const IDGOAL_ORDER = 0;

    const REVENUE_PRECISION = 2;

    const MAXIMUM_PRODUCT_CATEGORIES = 5;

    // In the GET items parameter, each item has the following array of information
    const INDEX_ITEM_SKU = 0;
    const INDEX_ITEM_NAME = 1;
    const INDEX_ITEM_CATEGORY = 2;
    const INDEX_ITEM_PRICE = 3;
    const INDEX_ITEM_QUANTITY = 4;

    // Used in the array of items, internally to this class
    const INTERNAL_ITEM_SKU = 0;
    const INTERNAL_ITEM_NAME = 1;
    const INTERNAL_ITEM_CATEGORY = 2;
    const INTERNAL_ITEM_CATEGORY2 = 3;
    const INTERNAL_ITEM_CATEGORY3 = 4;
    const INTERNAL_ITEM_CATEGORY4 = 5;
    const INTERNAL_ITEM_CATEGORY5 = 6;
    const INTERNAL_ITEM_PRICE = 7;
    const INTERNAL_ITEM_QUANTITY = 8;

    public $idGoal;
    public $requestIsEcommerce;
    private $isGoalAnOrder;

    /**
     * @var Action
     */
    protected $action = null;
    protected $convertedGoals = array();

    private $currentGoal = array();

    /**
     * @var Request
     */
    protected $request;
    protected $orderId;

    protected $isThereExistingCartInVisit = false;

    /**
     * Constructor
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->orderId = $request->getParam('ec_id');
        $this->idGoal  = $request->getParam('idgoal');

        $this->isGoalAnOrder = !empty($this->orderId);
        $this->requestIsEcommerce = (0 == $this->idGoal);
    }

    public function isGoalAnOrder()
    {
        return $this->isGoalAnOrder;
    }

    public function detectIsThereExistingCartInVisit($visitInformation)
    {
        if (!empty($visitInformation['visit_goal_buyer'])) {
            $goalBuyer = $visitInformation['visit_goal_buyer'];
            $types     = array(GoalManager::TYPE_BUYER_OPEN_CART, GoalManager::TYPE_BUYER_ORDERED_AND_OPEN_CART);

            // Was there a Cart for this visit prior to the order?
            $this->isThereExistingCartInVisit = in_array($goalBuyer, $types);
        }
    }

    public static function getGoalDefinitions($idSite)
    {
        $websiteAttributes = Cache::getCacheWebsiteAttributes($idSite);

        if (isset($websiteAttributes['goals'])) {
            return $websiteAttributes['goals'];
        }

        return array();
    }

    public static function getGoalDefinition($idSite, $idGoal)
    {
        $goals = self::getGoalDefinitions($idSite);

        foreach ($goals as $goal) {
            if ($goal['idgoal'] == $idGoal) {
                return $goal;
            }
        }

        throw new Exception('Goal not found');
    }

    public static function getGoalIds($idSite)
    {
        $goals   = self::getGoalDefinitions($idSite);
        $goalIds = array();

        foreach ($goals as $goal) {
            $goalIds[] = $goal['idgoal'];
        }

        return $goalIds;
    }

    /**
     * Look at the URL or Page Title and sees if it matches any existing Goal definition
     *
     * @param int $idSite
     * @param Action $action
     * @throws Exception
     * @return int Number of goals matched
     */
    public function detectGoalsMatchingUrl($idSite, $action)
    {
        if (!Common::isGoalPluginEnabled()) {
            return false;
        }

        $actionType = $action->getActionType();
        $goals = $this->getGoalDefinitions($idSite);

        foreach ($goals as $goal) {
            $attribute = $goal['match_attribute'];
            // if the attribute to match is not the type of the current action
            if ((($attribute == 'url' || $attribute == 'title') && $actionType != Action::TYPE_PAGE_URL)
              || ($attribute == 'file' && $actionType != Action::TYPE_DOWNLOAD)
              || ($attribute == 'external_website' && $actionType != Action::TYPE_OUTLINK)
              || ($attribute == 'manually')
              || in_array($attribute, array('event_action', 'event_name', 'event_category')) && $actionType != Action::TYPE_EVENT
            ) {
                continue;
            }


            switch ($attribute) {
                case 'title':
                    // Matching on Page Title
                    $url = $action->getActionName();
                    break;
                case 'event_action':
                    $url = $action->getEventAction();
                    break;
                case 'event_name':
                    $url = $action->getEventName();
                    break;
                case 'event_category':
                    $url = $action->getEventCategory();
                    break;
                // url, external_website, file, manually...
                default:
                    $url = $action->getActionUrlRaw();
                    break;
            }

            $pattern_type = $goal['pattern_type'];

            $match = $this->isUrlMatchingGoal($goal, $pattern_type, $url);
            if ($match) {
                $goal['url'] = $action->getActionUrl();
                $this->convertedGoals[] = $goal;
            }
        }

        return count($this->convertedGoals) > 0;
    }

    public function isManualGoalConversion()
    {
        return $this->idGoal > 0;
    }

    public function detectGoalId($idSite)
    {
        if (!Common::isGoalPluginEnabled()) {
            return false;
        }

        $goals = $this->getGoalDefinitions($idSite);

        if (!isset($goals[$this->idGoal])) {
            return false;
        }

        $goal = $goals[$this->idGoal];

        $url         = $this->request->getParam('url');
        $goal['url'] = PageUrl::excludeQueryParametersFromUrl($url, $idSite);
        $this->convertedGoals[] = $goal;

        return true;
    }

    /**
     * Records one or several goals matched in this request.
     *
     * @param Visitor $visitor
     * @param array $visitorInformation
     * @param array $visitCustomVariables
     * @param Action $action
     */
    public function recordGoals(Visitor $visitor, $visitorInformation, $visitCustomVariables, $action)
    {
        $goal = $this->getGoalFromVisitor($visitor, $visitorInformation, $action);

        // Copy Custom Variables from Visit row to the Goal conversion
        // Otherwise, set the Custom Variables found in the cookie sent with this request
        $goal += $visitCustomVariables;
        $maxCustomVariables = CustomVariables::getMaxCustomVariables();

        for ($i = 1; $i <= $maxCustomVariables; $i++) {
            if (isset($visitorInformation['custom_var_k' . $i])
                && strlen($visitorInformation['custom_var_k' . $i])
            ) {
                $goal['custom_var_k' . $i] = $visitorInformation['custom_var_k' . $i];
            }
            if (isset($visitorInformation['custom_var_v' . $i])
                && strlen($visitorInformation['custom_var_v' . $i])
            ) {
                $goal['custom_var_v' . $i] = $visitorInformation['custom_var_v' . $i];
            }
        }

        // some goals are converted, so must be ecommerce Order or Cart Update
        if ($this->requestIsEcommerce) {
            $this->recordEcommerceGoal($goal, $visitor, $action, $visitorInformation);
        } else {
            $this->recordStandardGoals($goal, $visitor, $action, $visitorInformation);
        }
    }

    /**
     * Returns rounded decimal revenue, or if revenue is integer, then returns as is.
     *
     * @param int|float $revenue
     * @return int|float
     */
    protected function getRevenue($revenue)
    {
        if (round($revenue) != $revenue) {
            $revenue = round($revenue, self::REVENUE_PRECISION);
        }

        $revenue = Common::forceDotAsSeparatorForDecimalPoint($revenue);

        return $revenue;
    }

    /**
     * Records an Ecommerce conversion in the DB. Deals with Items found in the request.
     * Will deal with 2 types of conversions: Ecommerce Order and Ecommerce Cart update (Add to cart, Update Cart etc).
     *
     * @param array $conversion
     * @param Visitor $visitor
     * @param Action $action
     * @param array $visitInformation
     */
    protected function recordEcommerceGoal($conversion, Visitor $visitor, $action, $visitInformation)
    {
        if ($this->isThereExistingCartInVisit) {
            Common::printDebug("There is an existing cart for this visit");
        }

        if ($this->isGoalAnOrder) {
            $debugMessage = 'The conversion is an Ecommerce order';

            $conversion['idorder'] = $this->orderId;
            $conversion['idgoal']  = self::IDGOAL_ORDER;
            $conversion['buster']  = Common::hashStringToInt($this->orderId);

            $conversionDimensions = ConversionDimension::getAllDimensions();
            $conversion = $this->triggerHookOnDimensions($conversionDimensions, 'onEcommerceOrderConversion', $visitor, $action, $conversion);
        } // If Cart update, select current items in the previous Cart
        else {
            $debugMessage = 'The conversion is an Ecommerce Cart Update';

            $conversion['buster'] = 0;
            $conversion['idgoal'] = self::IDGOAL_CART;

            $conversionDimensions = ConversionDimension::getAllDimensions();
            $conversion = $this->triggerHookOnDimensions($conversionDimensions, 'onEcommerceCartUpdateConversion', $visitor, $action, $conversion);
        }

        Common::printDebug($debugMessage . ':' . var_export($conversion, true));

        // INSERT or Sync items in the Cart / Order for this visit & order
        $items = $this->getEcommerceItemsFromRequest();

        if (false === $items) {
            return;
        }

        $itemsCount = 0;
        foreach ($items as $item) {
            $itemsCount += $item[GoalManager::INTERNAL_ITEM_QUANTITY];
        }

        $conversion['items'] = $itemsCount;

        if ($this->isThereExistingCartInVisit) {
            $recorded = $this->getModel()->updateConversion($visitInformation['idvisit'], self::IDGOAL_CART, $conversion);
        } else {
            $recorded = $this->insertNewConversion($conversion, $visitInformation);
        }

        if ($recorded) {
            $this->recordEcommerceItems($conversion, $items, $visitInformation);
        }

        /**
         * Triggered after successfully persisting an ecommerce conversion.
         *
         * _Note: Subscribers should be wary of doing any expensive computation here as it may slow
         * the tracker down._
         *
         * @param array $conversion The conversion entity that was just persisted. See what information
         *                          it contains [here](/guides/persistence-and-the-mysql-backend#conversions).
         * @param array $visitInformation The visit entity that we are tracking a conversion for. See what
         *                                information it contains [here](/guides/persistence-and-the-mysql-backend#visits).
         */
        Piwik::postEvent('Tracker.recordEcommerceGoal', array($conversion, $visitInformation));
    }

    /**
     * Returns Items read from the request string
     * @return array|bool
     */
    private function getEcommerceItemsFromRequest()
    {
        $items = $this->request->getParam('ec_items');

        if (empty($items)) {
            Common::printDebug("There are no Ecommerce items in the request");
            // we still record an Ecommerce order without any item in it
            return array();
        }

        if (!is_array($items)) {
            Common::printDebug("Error while json_decode the Ecommerce items = " . var_export($items, true));
            return false;
        }

        $items = Common::unsanitizeInputValues($items);

        $cleanedItems = $this->getCleanedEcommerceItems($items);
        return $cleanedItems;
    }

    /**
     * Loads the Ecommerce items from the request and records them in the DB
     *
     * @param array $goal
     * @param array $items
     * @throws Exception
     * @return int Number of items in the cart
     */
    protected function recordEcommerceItems($goal, $items)
    {
        $itemInCartBySku = array();
        foreach ($items as $item) {
            $itemInCartBySku[$item[0]] = $item;
        }

        $itemsInDb = $this->getModel()->getAllItemsCurrentlyInTheCart($goal, self::ITEM_IDORDER_ABANDONED_CART);

        // Look at which items need to be deleted, which need to be added or updated, based on the SKU
        $skuFoundInDb = $itemsToUpdate = array();

        foreach ($itemsInDb as $itemInDb) {
            $skuFoundInDb[] = $itemInDb['idaction_sku'];

            // Ensure price comparisons will have the same assumption
            $itemInDb['price'] = $this->getRevenue($itemInDb['price']);
            $itemInDbOriginal = $itemInDb;
            $itemInDb = array_values($itemInDb);

            // Cast all as string, because what comes out of the fetchAll() are strings
            $itemInDb = $this->getItemRowCast($itemInDb);

            //Item in the cart in the DB, but not anymore in the cart
            if (!isset($itemInCartBySku[$itemInDb[0]])) {
                $itemToUpdate = array_merge($itemInDb,
                    array('deleted'                => 1,
                          'idorder_original_value' => $itemInDbOriginal['idorder_original_value']
                    )
                );

                $itemsToUpdate[] = $itemToUpdate;
                Common::printDebug("Item found in the previous Cart, but no in the current cart/order");
                Common::printDebug($itemToUpdate);
                continue;
            }

            $newItem = $itemInCartBySku[$itemInDb[0]];
            $newItem = $this->getItemRowCast($newItem);

            if (count($itemInDb) != count($newItem)) {
                Common::printDebug("ERROR: Different format in items from cart and DB");
                throw new Exception(" Item in DB and Item in cart have a different format, this is not expected... " . var_export($itemInDb, true) . var_export($newItem, true));
            }
            Common::printDebug("Item has changed since the last cart. Previous item stored in cart in database:");
            Common::printDebug($itemInDb);
            Common::printDebug("New item to UPDATE the previous row:");
            $newItem['idorder_original_value'] = $itemInDbOriginal['idorder_original_value'];
            Common::printDebug($newItem);
            $itemsToUpdate[] = $newItem;
        }

        // Items to UPDATE
        $this->updateEcommerceItems($goal, $itemsToUpdate);

        // Items to INSERT
        $itemsToInsert = array();
        foreach ($items as $item) {
            if (!in_array($item[0], $skuFoundInDb)) {
                $itemsToInsert[] = $item;
            }
        }

        $this->insertEcommerceItems($goal, $itemsToInsert);
    }

    /**
     * Reads items from the request, then looks up the names from the lookup table
     * and returns a clean array of items ready for the database.
     *
     * @param array $items
     * @return array $cleanedItems
     */
    private function getCleanedEcommerceItems($items)
    {
        // Clean up the items array
        $cleanedItems = array();
        foreach ($items as $item) {
            $name     = $category = $category2 = $category3 = $category4 = $category5 = false;
            $price    = 0;
            $quantity = 1;

            // items are passed in the request as an array: ( $sku, $name, $category, $price, $quantity )
            if (empty($item[self::INDEX_ITEM_SKU])) {
                continue;
            }

            $sku = $item[self::INDEX_ITEM_SKU];
            if (!empty($item[self::INDEX_ITEM_NAME])) {
                $name = $item[self::INDEX_ITEM_NAME];
            }

            if (!empty($item[self::INDEX_ITEM_CATEGORY])) {
                $category = $item[self::INDEX_ITEM_CATEGORY];
            }

            if (isset($item[self::INDEX_ITEM_PRICE])
                && is_numeric($item[self::INDEX_ITEM_PRICE])
            ) {
                $price = $this->getRevenue($item[self::INDEX_ITEM_PRICE]);
            }
            if (!empty($item[self::INDEX_ITEM_QUANTITY])
                && is_numeric($item[self::INDEX_ITEM_QUANTITY])
            ) {
                $quantity = (int)$item[self::INDEX_ITEM_QUANTITY];
            }

            // self::INDEX_ITEM_* are in order
            $cleanedItems[] = array(
                self::INTERNAL_ITEM_SKU       => $sku,
                self::INTERNAL_ITEM_NAME      => $name,
                self::INTERNAL_ITEM_CATEGORY  => $category,
                self::INTERNAL_ITEM_CATEGORY2 => $category2,
                self::INTERNAL_ITEM_CATEGORY3 => $category3,
                self::INTERNAL_ITEM_CATEGORY4 => $category4,
                self::INTERNAL_ITEM_CATEGORY5 => $category5,
                self::INTERNAL_ITEM_PRICE     => $price,
                self::INTERNAL_ITEM_QUANTITY  => $quantity
            );
        }

        // Lookup Item SKUs, Names & Categories Ids
        $actionsToLookupAllItems = array();

        // Each item has 7 potential "ids" to lookup in the lookup table
        $columnsInEachRow = 1 + 1 + self::MAXIMUM_PRODUCT_CATEGORIES;

        foreach ($cleanedItems as $item) {
            $actionsToLookup = array();
            list($sku, $name, $category, $price, $quantity) = $item;
            $actionsToLookup[] = array(trim($sku), Action::TYPE_ECOMMERCE_ITEM_SKU);
            $actionsToLookup[] = array(trim($name), Action::TYPE_ECOMMERCE_ITEM_NAME);

            // Only one category
            if (!is_array($category)) {
                $actionsToLookup[] = array(trim($category), Action::TYPE_ECOMMERCE_ITEM_CATEGORY);
            } // Multiple categories
            else {
                $countCategories = 0;
                foreach ($category as $productCategory) {
                    $productCategory = trim($productCategory);
                    if (empty($productCategory)) {
                        continue;
                    }
                    $countCategories++;
                    if ($countCategories > self::MAXIMUM_PRODUCT_CATEGORIES) {
                        break;
                    }
                    $actionsToLookup[] = array($productCategory, Action::TYPE_ECOMMERCE_ITEM_CATEGORY);
                }
            }
            // Ensure that each row has the same number of columns, fill in the blanks
            for ($i = count($actionsToLookup); $i < $columnsInEachRow; $i++) {
                $actionsToLookup[] = array(false, Action::TYPE_ECOMMERCE_ITEM_CATEGORY);
            }
            $actionsToLookupAllItems = array_merge($actionsToLookupAllItems, $actionsToLookup);
        }

        $actionsLookedUp = TableLogAction::loadIdsAction($actionsToLookupAllItems);

        // Replace SKU, name & category by their ID action
        foreach ($cleanedItems as $index => &$item) {
            // SKU
            $item[0] = $actionsLookedUp[$index * $columnsInEachRow + 0];
            // Name
            $item[1] = $actionsLookedUp[$index * $columnsInEachRow + 1];
            // Categories
            $item[2] = $actionsLookedUp[$index * $columnsInEachRow + 2];
            $item[3] = $actionsLookedUp[$index * $columnsInEachRow + 3];
            $item[4] = $actionsLookedUp[$index * $columnsInEachRow + 4];
            $item[5] = $actionsLookedUp[$index * $columnsInEachRow + 5];
            $item[6] = $actionsLookedUp[$index * $columnsInEachRow + 6];
        }

        return $cleanedItems;
    }

    /**
     * Updates the cart items in the DB
     * that have been modified since the last cart update
     *
     * @param array $goal
     * @param array $itemsToUpdate
     *
     * @return void
     */
    protected function updateEcommerceItems($goal, $itemsToUpdate)
    {
        if (empty($itemsToUpdate)) {
            return;
        }

        Common::printDebug("Goal data used to update ecommerce items:");
        Common::printDebug($goal);

        foreach ($itemsToUpdate as $item) {
            $newRow = $this->getItemRowEnriched($goal, $item);
            Common::printDebug($newRow);

            $this->getModel()->updateEcommerceItem($item['idorder_original_value'], $newRow);
        }
    }

    private function getModel()
    {
        return new Model();
    }

    /**
     * Inserts in the cart in the DB the new items
     * that were not previously in the cart
     *
     * @param array $goal
     * @param array $itemsToInsert
     *
     * @return void
     */
    protected function insertEcommerceItems($goal, $itemsToInsert)
    {
        if (empty($itemsToInsert)) {
            return;
        }

        Common::printDebug("Ecommerce items that are added to the cart/order");
        Common::printDebug($itemsToInsert);

        $items = array();

        foreach ($itemsToInsert as $item) {
            $items[] = $this->getItemRowEnriched($goal, $item);
        }

        $this->getModel()->createEcommerceItems($items);
    }

    protected function getItemRowEnriched($goal, $item)
    {
        $newRow = array(
            'idaction_sku'       => (int)$item[self::INTERNAL_ITEM_SKU],
            'idaction_name'      => (int)$item[self::INTERNAL_ITEM_NAME],
            'idaction_category'  => (int)$item[self::INTERNAL_ITEM_CATEGORY],
            'idaction_category2' => (int)$item[self::INTERNAL_ITEM_CATEGORY2],
            'idaction_category3' => (int)$item[self::INTERNAL_ITEM_CATEGORY3],
            'idaction_category4' => (int)$item[self::INTERNAL_ITEM_CATEGORY4],
            'idaction_category5' => (int)$item[self::INTERNAL_ITEM_CATEGORY5],
            'price'              => Common::forceDotAsSeparatorForDecimalPoint($item[self::INTERNAL_ITEM_PRICE]),
            'quantity'           => $item[self::INTERNAL_ITEM_QUANTITY],
            'deleted'            => isset($item['deleted']) ? $item['deleted'] : 0, //deleted
            'idorder'            => isset($goal['idorder']) ? $goal['idorder'] : self::ITEM_IDORDER_ABANDONED_CART, //idorder = 0 in log_conversion_item for carts
            'idsite'             => $goal['idsite'],
            'idvisitor'          => $goal['idvisitor'],
            'server_time'        => $goal['server_time'],
            'idvisit'            => $goal['idvisit']
        );
        return $newRow;
    }

    public function getGoalColumn($column)
    {
        if (array_key_exists($column, $this->currentGoal)) {
            return $this->currentGoal[$column];
        }

        return false;
    }

    /**
     * Records a standard non-Ecommerce goal in the DB (URL/Title matching),
     * linking the conversion to the action that triggered it
     * @param $goal
     * @param Visitor $visitor
     * @param Action $action
     * @param $visitorInformation
     */
    protected function recordStandardGoals($goal, Visitor $visitor, $action, $visitorInformation)
    {
        foreach ($this->convertedGoals as $convertedGoal) {
            $this->currentGoal = $convertedGoal;
            Common::printDebug("- Goal " . $convertedGoal['idgoal'] . " matched. Recording...");
            $conversion = $goal;
            $conversion['idgoal'] = $convertedGoal['idgoal'];
            $conversion['url']    = $convertedGoal['url'];

            if (!is_null($action)) {
                $conversion['idaction_url'] = $action->getIdActionUrl();
                $conversion['idlink_va'] = $action->getIdLinkVisitAction();
            }

            // If multiple Goal conversions per visit, set a cache buster
            $conversion['buster'] = $convertedGoal['allow_multiple'] == 0
                ? '0'
                : $visitorInformation['visit_last_action_time'];

            $conversionDimensions = ConversionDimension::getAllDimensions();
            $conversion = $this->triggerHookOnDimensions($conversionDimensions, 'onGoalConversion', $visitor, $action, $conversion);

            $this->insertNewConversion($conversion, $visitorInformation);

            /**
             * Triggered after successfully recording a non-ecommerce conversion.
             *
             * _Note: Subscribers should be wary of doing any expensive computation here as it may slow
             * the tracker down._
             *
             * @param array $conversion The conversion entity that was just persisted. See what information
             *                          it contains [here](/guides/persistence-and-the-mysql-backend#conversions).
             */
            Piwik::postEvent('Tracker.recordStandardGoals', array($conversion));
        }
    }

    /**
     * Helper function used by other record* methods which will INSERT or UPDATE the conversion in the DB
     *
     * @param array $conversion
     * @param array $visitInformation
     * @return bool
     */
    protected function insertNewConversion($conversion, $visitInformation)
    {
        /**
         * Triggered before persisting a new [conversion entity](/guides/persistence-and-the-mysql-backend#conversions).
         *
         * This event can be used to modify conversion information or to add new information to be persisted.
         *
         * @param array $conversion The conversion entity. Read [this](/guides/persistence-and-the-mysql-backend#conversions)
         *                          to see what it contains.
         * @param array $visitInformation The visit entity that we are tracking a conversion for. See what
         *                                information it contains [here](/guides/persistence-and-the-mysql-backend#visits).
         * @param \Piwik\Tracker\Request $request An object describing the tracking request being processed.
         */
        Piwik::postEvent('Tracker.newConversionInformation', array(&$conversion, $visitInformation, $this->request));

        $newGoalDebug = $conversion;
        $newGoalDebug['idvisitor'] = bin2hex($newGoalDebug['idvisitor']);
        Common::printDebug($newGoalDebug);

        $wasInserted = $this->getModel()->createConversion($conversion);

        return $wasInserted;
    }

    /**
     * Casts the item array so that array comparisons work nicely
     * @param array $row
     * @return array
     */
    protected function getItemRowCast($row)
    {
        return array(
            (string)(int)$row[self::INTERNAL_ITEM_SKU],
            (string)(int)$row[self::INTERNAL_ITEM_NAME],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY2],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY3],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY4],
            (string)(int)$row[self::INTERNAL_ITEM_CATEGORY5],
            (string)$row[self::INTERNAL_ITEM_PRICE],
            (string)$row[self::INTERNAL_ITEM_QUANTITY],
        );
    }

    /**
     * @param $goal
     * @param $pattern_type
     * @param $url
     * @return bool
     * @throws \Exception
     */
    protected function isUrlMatchingGoal($goal, $pattern_type, $url)
    {
        $url = Common::unsanitizeInputValue($url);
        $goal['pattern'] = Common::unsanitizeInputValue($goal['pattern']);

        $match = $this->isGoalPatternMatchingUrl($goal, $pattern_type, $url);

        if (!$match) {
            // Users may set Goal matching URL as URL encoded
            $goal['pattern'] = urldecode($goal['pattern']);

            $match = $this->isGoalPatternMatchingUrl($goal, $pattern_type, $url);
        }
        return $match;
    }

    /**
     * @param ConversionDimension[] $dimensions
     * @param string $hook
     * @param Visitor $visitor
     * @param Action|null $action
     * @param array|null $valuesToUpdate If null, $this->visitorInfo will be updated
     *
     * @return array|null The updated $valuesToUpdate or null if no $valuesToUpdate given
     */
    private function triggerHookOnDimensions($dimensions, $hook, $visitor, $action, $valuesToUpdate)
    {
        foreach ($dimensions as $dimension) {
            $value = $dimension->$hook($this->request, $visitor, $action, $this);

            if (false !== $value) {
                if (is_float($value)) {
                    $value = Common::forceDotAsSeparatorForDecimalPoint($value);
                }

                $fieldName = $dimension->getColumnName();
                $visitor->setVisitorColumn($fieldName, $value);

                $valuesToUpdate[$fieldName] = $value;
            }
        }

        return $valuesToUpdate;
    }

    private function getGoalFromVisitor(Visitor $visitor, $visitorInformation, $action)
    {
        $goal = array(
            'idvisit'     => $visitorInformation['idvisit'],
            'idvisitor'   => $visitorInformation['idvisitor'],
            'server_time' => Date::getDatetimeFromTimestamp($visitorInformation['visit_last_action_time'])
        );

        $visitDimensions = VisitDimension::getAllDimensions();

        foreach ($visitDimensions as $dimension) {
            $value = $dimension->onAnyGoalConversion($this->request, $visitor, $action);
            if (false !== $value) {
                $goal[$dimension->getColumnName()] = $value;
            }
        }

        return $goal;
    }

    /**
     * @param $goal
     * @param $pattern_type
     * @param $url
     * @return bool
     * @throws Exception
     */
    protected function isGoalPatternMatchingUrl($goal, $pattern_type, $url)
    {
        switch ($pattern_type) {
            case 'regex':
                $pattern = $goal['pattern'];
                if (strpos($pattern, '/') !== false
                    && strpos($pattern, '\\/') === false
                ) {
                    $pattern = str_replace('/', '\\/', $pattern);
                }
                $pattern = '/' . $pattern . '/';
                if (!$goal['case_sensitive']) {
                    $pattern .= 'i';
                }
                $match = (@preg_match($pattern, $url) == 1);
                break;
            case 'contains':
                if ($goal['case_sensitive']) {
                    $matched = strpos($url, $goal['pattern']);
                } else {
                    $matched = stripos($url, $goal['pattern']);
                }
                $match = ($matched !== false);
                break;
            case 'exact':
                if ($goal['case_sensitive']) {
                    $matched = strcmp($goal['pattern'], $url);
                } else {
                    $matched = strcasecmp($goal['pattern'], $url);
                }
                $match = ($matched == 0);
                break;
            default:
                throw new Exception(Piwik::translate('General_ExceptionInvalidGoalPattern', array($pattern_type)));
                break;
        }
        return $match;
    }
}