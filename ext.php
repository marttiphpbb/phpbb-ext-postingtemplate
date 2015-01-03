<?php
/**
* phpBB Extension - marttiphpbb postingtemplate
* @copyright (c) 2014 marttiphpbb <info@martti.be>
* @license http://opensource.org/licenses/MIT
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
				$forums_table = $this->container->getParameter('tables.forums');

				$sql = 'SELECT forum_id FROM ' . $forums_table;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$config_text->delete('marttiphpbb_postingtemplate_forum[' . $row['forum_id'] . ']');
				}
				$db->sql_freeresult($result);
				return '1';
				break;
			default:
				return parent::purge_step($old_state);
				break;
		}
	}
}
