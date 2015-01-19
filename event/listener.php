<?php
/**
* phpBB Extension - marttiphpbb posting template
* @copyright (c) 2015 marttiphpbb <info@martti.be>
* @license http://opensource.org/licenses/MIT
*/

namespace marttiphpbb\postingtemplate\event;

use phpbb\config\db_text as config_text;
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

	/* @var request */
	protected $request;

	/* @var user */
	protected $user;

	/**
	* @param config_text		$config_text
	* @param request			$request
	* @param user				$user
	*/
	public function __construct(
			config_text $config_text,
			request $request,
			user $user
	)
	{
		$this->config_text = $config_text;
		$this->request = $request;
		$this->user = $user;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_manage_forums_update_data_after'	=> 'core_acp_manage_forums_update_data_after',
			'core.acp_manage_forums_display_form'		=> 'core_acp_manage_forums_display_form',
			'core.posting_modify_template_vars'			=> 'core_posting_modify_template_vars',
		);
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
