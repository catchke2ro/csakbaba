<?php

class CB_Resource_Db_Mptt extends CB_Resource_Db_Table
{
	/**
	 * Traversal tree information
	 * Values:
	 *  'left'          => column name for left value
	 *  'right'         => column name for right value
	 *  'column'        => column name for identifying row (primary key assumed)
	 *  'refColumn'     => column name for parent id (if not set, will look in reference map for own table match)
	 *  'order'         => order by for rebuilding tree (e.g. "`name` ASC, `age` DESC")
	 *
	 * @var array $_traversal
	 */
	protected $_traversal = array();
	/**
	 * Automatically is set to true once traversal info is set and verified
	 *
	 * @var boolean $_isTraversable
	 */
	protected $_isTraversable = false;
	/**
	 * Delete mode constants
	 */
	const DELETE_MAKE_NULL = '_deleteMakenull';
	const DELETE_RESTRICT = '_deleteRestrict';
	const DELETE_CASCADE = '_deleteCascade';
	const DELETE_REATTACH = '_deleteReattach';

	/**
	 * Construct Zend_Db_Table Object & verify traversal capabilities of table
	 *
	 */
	public function __construct ($config = array())
	{
		parent::__construct($config);
		$this->_initTraversal();
	}
	/**
	 * Returns columns names
	 *
	 * @return array columns
	 */
	public function getColumns ()
	{
		return $this->info(Zend_Db_Table_Abstract::COLS);
	}
	/**
	 * Returns the table name and schema separated by a dot for use in sql queries
	 *
	 * @return string schema.name || name
	 */
	public function getName ()
	{
		return $this->_schema ? $this->_schema . '.' . $this->_name : $this->_name;
	}
	/**
	 * Override delete method, calls _delete prefixed function dependent on $mode
	 * @param integer $id
	 * @param const $mode
	 * @param mixed $newParent
	 */
	public function delete ($id, $mode = self::DELETE_RESTRICT, $newParent = null)
	{}
	/**
	 * Override insert method - calls _insertTraversable
	 *
	 * @param mixed $data
	 * @return primary key
	 */
	public function insert (array $data)
	{}
	/**
	 * Override update method, if $newParent is specified rebuild traversal data
	 *
	 * @param mixed $data
	 * @param mixed $where
	 * @param mixed $newParent
	 * @return int
	 */
	public function update (array $data, $where, $newParent = false)
	{}
	/**
	 * Public function to rebuild tree traversal. The recursive function
	 * _rebuildTreeTraversal() must be called without arguments.
	 *
	 * @return $this - Fluent interface
	 */
	public function rebuildTreeTraversal ()
	{
		$this->_rebuildTreeTraversal();
		return $this;
	}
	/**
	 * Recursively rebuilds the modified preorder tree traversal
	 * data based on a parent id column
	 *
	 * @param int $parentId
	 * @param int $leftValue
	 * @return int new right value
	 */
	protected function _rebuildTreeTraversal ($parentId = null, $leftValue = 0)
	{}
	/**
	 * Calculates left and right values for new row and inserts it.
	 * Also adjusts all affected rows to make room for the new row.
	 *
	 * @param array $data
	 * @return int $id
	 */
	protected function _insertTraversable($data)
	{}
	/**
	 * Fetches all descendents of a given node
	 *
	 * @param Zend_Db_Table_Row_Abstract|string $row - Row object or value of row id
	 * @param Zend_Db_Select $select - optional custom select object
	 * @return Zend_Db_Table_Rowset|null
	 */
	public function fetchAllDescendents ($row, Zend_Db_Select $select = null)
	{}
	/**
	 * Fetches all descendents of a given node and returns them as a tree
	 *
	 * @param Zend_Db_Table_Row_Abstract|string|int $rows- Row object or value of row id or array of rows
	 * @param Zend_Db_Select $select - optional select object
	 * @return Zend_Db_Table_Rowset|null
	 */
	public function fetchTree ($row = null, Zend_Db_Select $select = null)
	{}
	/**
	 * Fetches all ancestors of a given node
	 *
	 * @param Zend_Db_Table_Row_Abstract|string $row - Row object or value of row id
	 * @param Zend_Db_Select $select - optional custom select object
	 * @return Zend_Db_Table_Rowset|null
	 */
	public function fetchAllAncestors ($row, Zend_Db_Select $select = null)
	{}
	/**
	 * Initialise Traversal, Verify that cols supplied in $_traversal exist and are of the correct type
	 *
	 * @return void
	 */
	protected function _initTraversal ()
	{}
	/**
	 * Verifies that the current table is traversable
	 *
	 * @throws Zend_Db_Exception - Table is not traversable
	 */
	protected function _verifyTraversable ()
	{
		if (! $this->_isTraversable) {
			require_once 'Zend/Db/Table/Mptt/Exception.php';
			throw new Zend_Db_Table_Mptt_Exception("Table {$this->_name} is not traversable");
		}
	}

	/**
	 * Make all direct child nodes root nodes, retaining their subtrees
	 */
	protected function _deleteMakenull()
	{}
	/**
	 * Prevent node deletion if node has children
	 */
	protected function _deleteRestrict()
	{}
	/**
	 * Delete a node and all its child nodes recursively
	 */
	protected function _deleteCascade()
	{}
	/**
	 * Attach all child nodes to node with specified parent id
	 */
	protected function _deleteReattach()
	{}
}