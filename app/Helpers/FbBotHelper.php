<?php

namespace App\Helpers;

use App\FbBotUser;
use App\FbBotMessage;
use GuzzleHttp\Client;

class FbBotHelper
{
    /**
     * Get user profile.
     *
     * @param  string fb_user_id
     *
     * @return array
     */
    public static function getUserProfile($fb_user_id)
    {
        $client = new Client();
        $res = $client->get(
            'https://graph.facebook.com/v2.6/'.$fb_user_id.
            '?fields=first_name,last_name,profile_pic,locale,timezone,gender'.
            '&access_token='.config('services.botman.facebook_token'),
            []
        );

        return json_decode($res->getBody(), true);
    }

    /**
     * Record fb user.
     *
     * @param  object bot
     *
     * @return object fb_bot_user
     */
    public static function recordFbUser($bot)
    {
        $fb_user = $bot->getUser();
        $fb_bot_user = FbBotUser::where('fb_user_id', $fb_user->getId())
                        ->first();
        if ($fb_bot_user === null) {
            $fb_user_profile = self::getUserProfile($fb_user->getId());
            $fb_bot_user = FbBotUser::create([
                'fb_user_id' => $fb_user->getId(),
                'first_name' => $fb_user_profile['first_name'],
                'last_name' => $fb_user_profile['last_name'],
                'profile_pic' => $fb_user_profile['profile_pic'],
                'locale' => $fb_user_profile['locale'],
                'timezone' => $fb_user_profile['timezone'],
                'gender' => $fb_user_profile['gender'],
            ]);
        }

        return $fb_bot_user;
    }

    /**
     * Record fb message.
     *
     * @param  object bot
     *
     * @return sender_id
     */
    public static function recordFbMessage($bot)
    {
        $fb_user = $bot->getUser();
        $fb_user_id = $fb_user->getId();
        if ($fb_user_id != null) {
            $fb_bot_message = FbBotMessage::create([
                'sender' => $bot->getMessage()->getPayload()['sender']['id'],
                'recipient' => $bot->getMessage()->getPayload()['recipient']['id'],
                'message' => json_encode($bot->getMessage()->getPayload()),
            ]);

            return $bot->getMessage()->getPayload()['sender']['id'];
        } else {
            return;
        }
    }

    /**
     * Record fb template reply message.
     *
     * @param  object bot
     * @param  string message
     */
    public static function recordFbTemplateReplyMessage($bot, $message)
    {
        $fb_user = $bot->getUser();
        $fb_user_id = $fb_user->getId();
        if ($fb_user_id != null) {
            $fb_bot_message = FbBotMessage::create([
                'sender' => '635531853285108',
                'recipient' => $fb_user_id,
                'message' => $message,
            ]);
        }
    }
}
