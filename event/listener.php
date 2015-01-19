<?php
/**
* phpBB Extension - marttiphpbb posting template
* @copyright (c) 2015 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\postingtemplate\event;

use phpbb\config\db_text as config_text;
use phpbb\db\driver\factory as db;
use phpbb\request\request;
use phpbb\user;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var config_text */
	protected $config_text;

	/** @var db **/
	protected $db;

	/* @var request */
	protected $request;

	/* @var user */
	protected $user;

	/* @var string */
	protected $config_text_table;

	/* @var string */
	protected $forums_table;

	/**
	* @param config_text		$config_text
	* @param db					$db
	* @param request			$request
	* @param user				$user
	* @param string				$config_text_table
	* @param string				$forums_table
	*/
	public function __construct(
			config_text $config_text,
			db $db,
			request $request,
			user $user,
			$config_text_table,
			$forums_table
	)
	{
		$this->config_text = $config_text;
		$this->db = $db;
		$this->request = $request;
		$this->user = $user;
		$this->config_text_table = $config_text_table;
		$this->forums_table = $forums_table;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_manage_forums_initialise_data'	=> 'core_acp_manage_forums_initialise_data',
			'core.acp_manage_forums_update_data_after'	=> 'core_acp_manage_forums_update_data_after',
			'core.acp_manage_forums_display_form'		=> 'core_acp_manage_forums_display_form',
			'core.posting_modify_template_vars'			=> 'core_posting_modify_template_vars',
		);
	}

	public function core_acp_manage_forums_initialise_data($event)
	{
		// because there's no php event in delete forums,
		// we do cleanup of posting templates of deleted forums whenever showing the form.
		$sql = 'SELECT config_name
			FROM ' . $this->config_text_table . '
			WHERE config_name ' . $this->db->sql_like_expression('marttiphpbb_postingtemplate_forum' . $this->db->get_any_char());
		$result = $this->db->sql_query($sql);
		$postingtemplates = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		if (sizeof($postingtemplates))
		{
			$all_forums = array();
			$sql = 'SELECT forum_id FROM ' . $this->forums_table;
			$result = $this->db->sql_query($sql);
			while ($forum_id = $this->db->sql_fetchfield('forum_id'))
			{
				$all_forums[$forum_id] = 1;
			}
			$this->db->sql_freeresult($result);

			$to_delete_ary = array();

			foreach ($postingtemplates as $name)
			{
				$name = $name['config_name'];
				$forum_id = trim(substr($name, strpos($name, '[') + 1), ']');
				if (!isset($all_forums[$forum_id]))
				{
					$to_delete_ary[] = $name;
				}
			}

			$this->config_text->delete_array($to_delete_ary);
		}
	}

	public function core_acp_manage_forums_update_data_after($event)
	{
		$forum_data = $event['forum_data'];
		$forum_id = $forum_data['forum_id'];

		$postingtemplate = utf8_normalize_nfc($this->request->variable('forum_postingtemplate', '', true));

		if ($postingtemplate)
		{
			$this->config_text->set('marttiphpbb_postingtemplate_forum[' . $forum_id . ']', $postingtemplate);
		}
		else
		{
			$this->config_text->delete('marttiphpbb_postingtemplate_forum[' . $forum_id . ']');
		}
	}

	public function core_acp_manage_forums_display_form($event)
	{
		$action = $event['action'];
		$forum_id = $event['forum_id'];
		$template_data = $event['template_data'];

		$postingtemplate = ($action == 'add') ? '' : $this->config_text->get('marttiphpbb_postingtemplate_forum[' . $forum_id . ']');

		$template_data['FORUM_POSTINGTEMPLATE'] = ($postingtemplate) ? $postingtemplate : '';

		$event['template_data'] = $template_data;

		$this->user->add_lang_ext('marttiphpbb/postingtemplate', 'acp');
	}

	public function core_posting_modify_template_vars($event)
	{
		$page_data = $event['page_data'];
		$post_data = $event['post_data'];
		$mode = $event['mode'];
		$submit = $event['submit'];
		$preview = $event['preview'];
		$load = $event['load'];
		$save = $event['save'];
		$refresh = $event['refresh'];
		$forum_id = $event['forum_id'];

		if ($mode == 'post'
			&& !$submit && !$preview && !$load && !$save && !$refresh
			&& empty($post_data['post_text']) && empty($post_data['post_subject'])
			&& $this->config_text->get('marttiphpbb_postingtemplate_forum[' . $forum_id . ']'))
		{
			$page_data['MESSAGE'] = $this->config_text->get('marttiphpbb_postingtemplate_forum[' . $forum_id . ']');
		}

		$event['page_data'] = $page_data;
	}
}
