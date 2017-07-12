<?php

namespace App\Http\Controllers;

use App;
use Config;
use Mpociot\BotMan\BotMan;
use App\ObeobekoRepository;
use App\Helpers\FbBotHelper;
use App\Helpers\YoutubeHelper;
use Mpociot\BotMan\Facebook\Element;
use Irazasyed\LaravelGAMP\Facades\GAMP;
use Mpociot\BotMan\Facebook\ElementButton;
use Mpociot\BotMan\Facebook\GenericTemplate;

class BotController extends Controller
{
    public function fbBotTrack($bot)
    {
        $sender_id = FbBotHelper::recordFbMessage($bot);
        if ($sender_id != null && $sender_id != '635531853285108') {
            $fb_user_profile = FbBotHelper::getUserProfile($sender_id);
            $accept_support_mapping = Config::get('languages.accept_support_mapping');
            if (array_key_exists($fb_user_profile['locale'], $accept_support_mapping)) {
                $locale = $accept_support_mapping[$fb_user_profile['locale']];
            } else {
                $locale = 'en';
            }
            App::setLocale($locale);
        }

        return $sender_id;
    }

    public function fbBotSendObeObeKo($bot)
    {
        $obeobeko = ObeobekoRepository::getRandomObeobeko();
        $bot->reply(GenericTemplate::create()
            ->addElements([
                Element::create($obeobeko->title)
                    ->subtitle($obeobeko->content)
                    ->image($obeobeko->getCoverUrl(480))
                    ->addButton(ElementButton::create(trans('obeobeko.go_to_play'))
                        ->url($obeobeko->getUrl().
                        '?utm_source=fb_bot&utm_medium=please_obeobeko')),
            ]));
        $bot->typesAndWaits(1);
        $bot->reply(trans('obeobeko.thanks_the_obeobeker', ['name' => $obeobeko->owner->name]));
        FbBotHelper::recordFbTemplateReplyMessage($bot, $obeobeko->getUrl());
    }

    public function fbBotSendYoutube($bot, $query)
    {
        $json_array = YoutubeHelper::search($query);
        $random_index = rand(0, count($json_array['items']) - 1);

        if (! isset($json_array['items'][$random_index])) {
            $bot->reply(trans('obeobeko.cant_find_any_on_youtube', ['query' => $query]));
        } else {
            $youtube_video = $json_array['items'][$random_index];
            if (isset($youtube_video['snippet']['thumbnails']['standard'])) {
                $thumbnail_url = $youtube_video['snippet']['thumbnails']['standard']['url'];
            } else {
                $thumbnail_url = $youtube_video['snippet']['thumbnails']['high']['url'];
            }
            $bot->reply(trans('obeobeko.find_some_music'));
            $bot->typesAndWaits(1);
            $bot->reply(trans('obeobeko.obeobeko_bot').'!');
            $bot->typesAndWaits(1);
            $bot->reply(GenericTemplate::create()
                ->addElements([
                    Element::create($youtube_video['snippet']['title'])
                        ->subtitle($youtube_video['snippet']['description'])
                        ->image($thumbnail_url)
                        ->addButton(ElementButton::create(trans('obeobeko.go_to_play'))
                            ->url(url('/youtube/'.$youtube_video['id']['videoId']).
                                '?utm_source=fb_bot&utm_medium=youtube_query'))
                        ->addButton(ElementButton::create(trans('obeobeko.publish_to_my_obeobeko'))
                            ->url(url('/dashboard/create-obeobeko?youtube_id='.
                                $youtube_video['id']['videoId']).
                                '&utm_source=fb_bot&utm_medium=youtube_query'))
                        ->addButton(ElementButton::create(trans('obeobeko.related_music'))
                            ->payload('RELATED_MUSIC')->type('postback')),
                ]));
            $bot->typesAndWaits(1);
            $bot->reply(trans('obeobeko.try_it'));
            FbBotHelper::recordFbTemplateReplyMessage($bot, 'Send youtube '.$youtube_video['id']['videoId']);

            $bot->userStorage()->save([
                'youtube_id' => $youtube_video['id']['videoId'],
            ]);
        }
    }

    public function fbBotSendYoutubeRelated($bot)
    {
        $bot_user = $bot->userStorage()->get();
        if ($bot_user->has('youtube_id')) {
            $youtube_id = $bot_user->get('youtube_id');
            $json_array = YoutubeHelper::related($youtube_id);
            if (count($json_array['items']) == 0) {
                $bot->reply(trans('obeobeko.cant_find_any_related'));
            } else {
                $elements_array = [];
                foreach ($json_array['items'] as $youtube_video) {
                    if (isset($youtube_video['snippet']['thumbnails']['standard'])) {
                        $thumbnail_url = $youtube_video['snippet']['thumbnails']['standard']['url'];
                    } else {
                        $thumbnail_url = $youtube_video['snippet']['thumbnails']['high']['url'];
                    }
                    $elements_array[] = Element::create($youtube_video['snippet']['title'])
                        ->subtitle($youtube_video['snippet']['description'])
                        ->image($thumbnail_url)
                        ->addButton(ElementButton::create(trans('obeobeko.go_to_play'))
                            ->url(url('/youtube/'.$youtube_video['id']['videoId']).
                            '?utm_source=fb_bot&utm_medium=youtube_related'))
                        ->addButton(ElementButton::create(trans('obeobeko.publish_to_my_obeobeko'))
                            ->url(url('/dashboard/create-obeobeko?youtube_id='.
                            $youtube_video['id']['videoId']).
                            '&utm_source=fb_bot&utm_medium=youtube_related'));
                }
                $bot->reply(GenericTemplate::create()
                    ->addElements($elements_array));
                $bot->typesAndWaits(1);
                $bot->reply(trans('obeobeko.try_it'));
            }
        } else {
            $bot->reply(trans('obeobeko.please_use_youtube_cmd_at_first'));
        }
    }

    public function fbBot()
    {
        $botman = app('botman');
        $botman->verifyServices('MY_SECRET_VERIFICATION_TOKEN');

        $botman->hears('GET_STARTED_PAYLOAD', function ($bot) {
            $sender_id = $this->fbBotTrack($bot);
            $fb_bot_user = FbBotHelper::recordFbUser($bot);
            $bot->reply(trans('obeobeko.fb_bot_welcome', ['fb_username' => $fb_bot_user->first_name]));
            $bot->typesAndWaits(1);
            $bot->reply(trans('obeobeko.fb_bot_instruct'));
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/get-started');
                $gamp->sendPageview();
            }*/
        });

        $botman->hears('HOW_TO_USE_PAYLOAD', function ($bot) {
            $sender_id = $this->fbBotTrack($bot);
            $bot->typesAndWaits(1);
            $bot->reply(trans('obeobeko.obeobeko_bot_is_easy_to_use'));
            $bot->typesAndWaits(1);
            $bot->reply(trans('obeobeko.easy_to_use_1'));
            $bot->typesAndWaits(1);
            $bot->reply(trans('obeobeko.easy_to_use_2'));
            $bot->typesAndWaits(1);
            $bot->reply(trans('obeobeko.easy_to_use_3'));
            $bot->typesAndWaits(1);
            $bot->reply(trans('obeobeko.have_fun'));
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/how-to-use');
                $gamp->sendPageview();
            }*/
        });

        $botman->hears('PLEASE_OBEOBEKO_PAYLOAD', function ($bot) {
            $sender_id = $this->fbBotTrack($bot);
            $bot->typesAndWaits(1);
            $this->fbBotSendObeObeKo($bot);
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/please-obeobeko');
                $gamp->sendPageview();
            }*/
        });

        $botman->hears('(.*)請謳歌(.*)', function ($bot) {
            $sender_id = $this->fbBotTrack($bot);
            $bot->typesAndWaits(1);
            $this->fbBotSendObeObeKo($bot);
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/please-obeobeko');
                $gamp->sendPageview();
            }*/
        });

        $botman->hears('(.*)Please ObeObeKo(.*)', function ($bot) {
            $sender_id = $this->fbBotTrack($bot);
            $bot->typesAndWaits(1);
            $this->fbBotSendObeObeKo($bot);
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/please-obeobeko');
                $gamp->sendPageview();
            }*/
        });

        $botman->hears('youtube {query}', function ($bot, $query) {
            $sender_id = $this->fbBotTrack($bot);
            $bot->typesAndWaits(1);
            $this->fbBotSendYoutube($bot, $query);
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/youtube_'.$query);
                $gamp->sendPageview();
            }*/
        });

        $botman->hears('(.*)相關音樂(.*)', function ($bot) {
            $sender_id = $this->fbBotTrack($bot);
            $bot->typesAndWaits(1);
            $this->fbBotSendYoutubeRelated($bot);
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/youtube_related');
                $gamp->sendPageview();
            }*/
        });

        $botman->hears('(.*)Related Music(.*)', function ($bot) {
            $sender_id = $this->fbBotTrack($bot);
            $bot->typesAndWaits(1);
            $this->fbBotSendYoutubeRelated($bot);
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/youtube_related');
                $gamp->sendPageview();
            }*/
        });

        $botman->hears('RELATED_MUSIC', function ($bot) {
            $sender_id = $this->fbBotTrack($bot);
            $bot->typesAndWaits(1);
            $this->fbBotSendYoutubeRelated($bot);
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/youtube_related');
                $gamp->sendPageview();
            }*/
        });

        $botman->fallback(function ($bot) {
            $sender_id = $this->fbBotTrack($bot);
            $bot->typesAndWaits(1);
            $bot->reply(trans('obeobeko.sorry_i_dont_understand'));
            /*if ($sender_id != null && $sender_id != '635531853285108') {
                $gamp = GAMP::setClientId($sender_id);
                $gamp->setDocumentPath('/api/bot/facebook/fallback');
                $gamp->sendPageview();
            }*/
        });

        // start listening
        $botman->listen();
    }
}
