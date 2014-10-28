<?php
/**
*
* @package liveSearch
* @copyright (c) alg
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace alg\newpmajax\migrations;


class v_1_0_0 extends \phpbb\db\migration\migration
{

	public function effectively_installed()
	{
		return isset($this->config['newpmajax']) && version_compare($this->config['newpmajax'], '1.0.0', '>=');
	}

	static public function depends_on()
	{
			return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return 	array();
	}

	public function revert_schema()
	{
		return 	array();
	}

	public function update_data()
	{
		return array(
			//  Remove old config
			// Current version
			array('config.add', array('newpmajax', '1.0.0')),

	



		);
	}
	public function revert_data()
	{
		return array(
			// remove from configs

			// Current version
			array('config.remove', array('newpmajax')),

		
		);
	}
}
