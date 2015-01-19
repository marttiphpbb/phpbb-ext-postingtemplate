<?php
/**
* phpBB Extension - marttiphpbb postingtemplate
* @copyright (c) 2015 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\postingtemplate;

/**
* @ignore
*/

class ext extends \phpbb\extension\base
{
	/**
	* @param mixed $old_state State returned by previous call of this method
	* @return mixed Returns false after last step, otherwise temporary state
	*/
	function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				// delete posting template data
				$config_text = $this->container->get('config_text');
				$db = $this->container->get('dbal.conn');
				$config_text_table = $this->container->getParameter('tables.config_text');

				// there's no method in the config_text service to retrieve the names with a sql like expression, so we do it with a query here.
				$sql = 'SELECT config_name
					FROM ' . $config_text_table . '
					WHERE config_name ' . $db->sql_like_expression('marttiphpbb_postingtemplate_forum' . $db->get_any_char());
				$result = $db->sql_query($sql);
				$postingtemplates = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);

				if (sizeof($postingtemplates))
				{
					$postingtemplates = array_map(function($row){
						return $row['config_name'];
					}, $postingtemplates);
					$config_text->delete_array($postingtemplates);
				}
				return '1';
				break;
			default:
				return parent::purge_step($old_state);
				break;
		}
	}
}
