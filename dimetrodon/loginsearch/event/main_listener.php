<?php
/**
 *
 * Login To Search. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, [Dimetrodon], https://phpbbforever.com/home
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dimetrodon\loginsearch\event;

use phpbb\auth\auth;
use phpbb\language\language;
use phpbb\template\twig\twig;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{

	public function __construct(
		private auth $auth,
		private language $language,
		private twig $twig,
		private user $user
	)
	{
	}

	public static function getSubscribedEvents(): array
	{
		return [
			'core.search_auth_checks_override' => 'search_auth',
		];
	}

	/**
	 * Override search auth setting
	 *
	 * @param \phpbb\event\data $event Event object
	 */
	public function search_auth($event): void
	{
		// Is user able to search?
		if (!$this->auth->acl_get('u_search'))
		{
			// Is the user logged in but unable to search? If so, they will get an error message.
			if ($this->user->data['user_id'] != ANONYMOUS)
			{
				$this->twig->assign_var('S_NO_SEARCH', true);
				trigger_error('NO_SEARCH');
			}

			$this->language->add_lang('common', 'dimetrodon/loginsearch');

			// If the user is a guest and cannot search, they will recieve a login page.
			login_box('', $this->language->lang('LOGIN_EXPLAIN_SEARCH'));
		}

		// Override auth setting
		$event['search_auth_check_override'] = true;
	}
}
