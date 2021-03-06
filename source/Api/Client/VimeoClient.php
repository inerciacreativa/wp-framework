<?php

namespace ic\Framework\Api\Client;

use ic\Framework\Api\Auth\AuthInterface;
use ic\Framework\Api\Auth\OAuthToken;
use ic\Framework\Data\Collection;
use ic\Framework\Framework;
use RuntimeException;

/**
 * Class VimeoClient
 *
 * @package ic\Framework\Api\Client
 */
class VimeoClient extends Client
{

	/**
	 * @inheritdoc
	 */
	public function getAuth(): ?AuthInterface
	{
		static $auth;
		if ($auth === null) {
			if (empty($this->credentials['id']) || empty($this->credentials['secret'])) {
				throw new RuntimeException('Could not find the credentials!');
			}

			$auth = new OAuthToken($this->getEndpoint('/oauth/authorize/client'), $this->credentials['id'], $this->credentials['secret'], $this->getHeaders());
		}

		return $auth;
	}

	/**
	 * @return array
	 */
	protected function getHeaders(): array
	{
		return ['Accept' => 'application/vnd.vimeo.*+json; version=' . $this->getVersion()];
	}

	/**
	 * @inheritdoc
	 */
	protected function getCredentials(): array
	{
		/** @noinspection NullPointerExceptionInspection */
		return Framework::instance()
						->getOptions()
						->get('vimeo.credentials', []);
	}

	/**
	 * @inheritdoc
	 */
	public function getName(): string
	{
		return 'Vimeo';
	}

	/**
	 * @inheritdoc
	 */
	public function getVersion(): string
	{
		return '3.2';
	}

	/**
	 * @inheritdoc
	 */
	public function getDomain(string $path = ''): string
	{
		return 'https://vimeo.com' . $path;
	}

	/**
	 * @inheritdoc
	 */
	public function getEndpoint(string $path = ''): string
	{
		return 'https://api.vimeo.com' . $path;
	}

	/**
	 * @inheritdoc
	 */
	public function getUrls(): array
	{
		static $urls;
		if ($urls === null) {
			$urls = [
				'video'         => $this->getDomain('/videos/#ID#'),
				'userVideos'    => $this->getDomain('/#ID#'),
				'channelVideos' => $this->getDomain('/channels/#ID#'),
				'groupVideos'   => $this->getDomain('/groups/#ID#'),
				'embed'         => 'https://player.vimeo.com/video/#ID#',
			];
		}

		return $urls;
	}

	/**
	 * @inheritdoc
	 */
	public function getMethods(): Collection
	{
		return Collection::make([
			[
				'name'     => 'video',
				'type'     => 'video',
				'label'    => __('Video', 'ic-framework'),
				'callback' => 'getVideo',
			],
			[
				'name'     => 'user',
				'type'     => 'info',
				'label'    => __('User', 'ic-framework'),
				'callback' => 'getUser',
			],
			[
				'name'     => 'userVideos',
				'type'     => 'video',
				'label'    => __('User Videos', 'ic-framework'),
				'callback' => 'getUserVideos',
			],
			[
				'name'     => 'channel',
				'type'     => 'info',
				'label'    => __('Channel', 'ic-framework'),
				'callback' => 'getChannel',
			],
			[
				'name'     => 'channelVideos',
				'type'     => 'video',
				'label'    => __('Channel Videos', 'ic-framework'),
				'callback' => 'getChannelVideos',
			],
			[
				'name'     => 'group',
				'type'     => 'info',
				'label'    => __('Group', 'ic-framework'),
				'callback' => 'getGroup',
			],
			[
				'name'     => 'groupVideos',
				'type'     => 'video',
				'label'    => __('Group Videos', 'ic-framework'),
				'callback' => 'getGroupVideos',
			],
		]);
	}

	/**
	 * @param string $videoId
	 *
	 * @return null|object
	 */
	public function getVideo(string $videoId): ?object
	{
		return $this->getApi()->get("videos/$videoId");
	}

	/**
	 * @param string $userId
	 *
	 * @return null|object
	 */
	public function getUser(string $userId): ?object
	{
		return $this->getApi()->get("users/$userId");
	}

	/**
	 * @param string $userId
	 * @param int    $maxResults
	 *
	 * @return null|object
	 */
	public function getUserVideos(string $userId, int $maxResults = 10): ?object
	{
		return $this->getApi()
					->get("users/$userId/videos", ['per_page' => $maxResults]);
	}

	/**
	 * @param string $channelId
	 *
	 * @return null|object
	 */
	public function getChannel(string $channelId): ?object
	{
		return $this->getApi()->get("channels/$channelId");
	}

	/**
	 * @param string $channelId
	 * @param int    $maxResults
	 *
	 * @return null|object
	 */
	public function getChannelVideos(string $channelId, int $maxResults = 10): ?object
	{
		return $this->getApi()
					->get("channels/$channelId/videos", ['per_page' => $maxResults]);
	}

	/**
	 * @param string $groupId
	 *
	 * @return null|object
	 */
	public function getGroup(string $groupId): ?object
	{
		return $this->getApi()->get("groups/$groupId");
	}

	/**
	 * @param string $groupId
	 * @param int    $maxResults
	 *
	 * @return null|object
	 */
	public function getGroupVideos(string $groupId, int $maxResults = 10): ?object
	{
		return $this->getApi()
					->get("groups/$groupId/videos", ['per_page' => $maxResults]);
	}

}
