<?php
/**
 * The data interface helper.
 *
 * @package StoreLocatorPlus\Tagalong\Data
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2013 Charleston Software Associates, LLC
 *
 */
class Tagalong_Data {

    /**
     * The global WordPress DB
     *
     * @var object $db
     */
    var $db;

    /**
     * Properties of the plugin data table.
     *
     * 'name'   = table name
     * 'fields' = key/value pair key = field name, value = field format
     *
     * @var string[] $plugintable
     */
    var $plugintable;

    /**
     * The collate modifier for create table commands.
     *
     * @var string $collate 
     */
    var $collate;

    /**
     * The parent plugin.
     *
     * @var \SLPTagalong $addon
     */
    var $addon;

	/**
	 * The base SLP plugin.
	 *
	 * @var \SLPlus
	 */
	var $slplus;

    //-------------------------------------------------
    // Methods
    //-------------------------------------------------

    /**
     * Initialize a new location
     *
     * Table: wp_slp_more_location_data
     *     sl_id        - the store location ID from wp_store_locator table
     *     term_id      - the category ID from the wp_terms table
     *
     * @param mixed[] $params - a named array of the plugin options.
     */
    public function __construct($params = null) {
        if (($params != null) && is_array($params)) {
            foreach ($params as $key=>$value) {
                $this->$key = $value;
            }
        }
	    $this->db = $this->slplus->db;

        // Set the plugin details table properties
        //
        $this->plugintable['name'] = $this->db->prefix . 'slp_tagalong';
        $this->plugintable['fields'] = array(
            'sl_id'     => '%u',
            'term_id'   => '%u'
        );
    }

    /**
     *Return a record as an array based on a given SQL select statement keys and params list.
     *
     * @param string[] $commandList
     * @param mixed[] $params
     * @param int $offset
     */
    function get_Record($commandList,$params=array(),$offset=0) {
        return
            $this->db->get_row(
                $this->db->prepare(
                    $this->get_SQL($commandList),
                    $params
                    ),
                ARRAY_A,
                $offset
            );
    }

	/**
	 * Set the database character set
	 */
	function set_DB_charset() {
		$collate = '';
		if( $this->db->has_cap( 'collation' ) ) {
			if( ! empty($this->db->charset ) ) $collate .= "DEFAULT CHARACTER SET {$this->db->charset}";
			if( ! empty($this->db->collate ) ) $collate .= " COLLATE {$this->db->collate}";
		}
		$this->collate   = $collate;
	}

    /**
     * Get an SQL statement for this database.
     *
     * @param string[] $commandList
     * @return string
     */
    function get_SQL($commandList) {
        $sqlStatement = '';
        if (!is_array($commandList)) { $commandList = array($commandList); }
        foreach ($commandList as $command) {
            switch ($command) {
                case 'create_tagalong_helper':
                    $sqlStatement .=
                        "CREATE TABLE {$this->plugintable['name']} (
                        sl_id mediumint(8) unsigned NOT NULL,
                        term_id bigint(20) unsigned NOT NULL,
                        KEY sl_id (sl_id),
                        KEY term_id (term_id),
                        KEY sl_term_id (sl_id,term_id)
                        ) {$this->collate}";
                    break;

                case 'delete_category_by_id':
                    $sqlStatement .=
                        'DELETE FROM ' . $this->plugintable['name'] .
                        ' WHERE sl_id = %u '
                        ;
                    break;

                case 'select_categorycount_for_location':
                    $sqlStatement .=
                        'SELECT count(sl_id) FROM ' .  $this->plugintable['name'] . ' ' .
                        'WHERE '.$this->slplus->database->info['table'].'.sl_id='.$this->plugintable['name'].'.sl_id'
                        ;
                    break;

                case 'tagalong_selectall':
                    $sqlStatement .= 'SELECT * FROM ' . $this->plugintable['name'];
                    break;

                case 'select_categories_for_location' :
                    $sqlStatement .= 
                        'SELECT term_id FROM ' . $this->plugintable['name'] .
                        ' WHERE sl_id = %u'                                 ;
                    break;
                
                case 'select_by_keyandcat':
                    $sqlStatement .=
                        'SELECT * FROM ' . $this->plugintable['name']   .
                        ' WHERE sl_id = %u AND term_id = %u'            ;
                    break;

                case 'select_where_optionvalue_has_cats':
                    $sqlStatement .=
                        'SELECT sl_id, sl_option_value FROM ' . $this->slplus->database->info['table']  . ' ' .
                        "WHERE sl_option_value LIKE '%store_categories%'"                  ;
                    break;

                case 'whereslid':
                    $sqlStatement .= ' WHERE sl_id = %u ';
                    break;

                default:
                    return $command;
                    break;
            }
        }

        return $sqlStatement;
    }

    /**
     * Add a record to the plugin info table if it does not exist.
     *
     * @param string $location_id
     * @param string $category_id
     * @return null
     */
    function add_RecordIfNeeded($location_id=null, $category_id=null) {
        if (!isset($this->addon)) { return; }

        // Check if record exists
        //
        $matching_record = $this->get_Record('select_by_keyandcat',array($location_id,$category_id));

        // Add record if it does not exist
        //
        if (
            ( $matching_record === null )                   ||
            ( $matching_record['sl_id'] != $location_id )
            ){
            $this->db->insert($this->plugintable['name'],
                            array(
                            'sl_id'   => $location_id,
                            'term_id' => $category_id
                            )
                    );
        }

       return;
    }
}
