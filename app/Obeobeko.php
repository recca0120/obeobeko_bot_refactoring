<?php

namespace App;

use Uuid;
use Carbon\Carbon;
use App\Helpers\ImageHelper;
use App\Helpers\StringHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obeobeko extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'youtube_item_id', 'title', 'content', 'cover', 'cover_type',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * Get the obeobeko belong to owner.
     */
    public function owner()
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }

    /**
     * Get the obeobeko belong to youtube_item.
     */
    public function youtubeItem()
    {
        return $this->belongsTo(\App\YoutubeItem::class, 'youtube_item_id', 'id');
    }

    /**
     * Get url.
     *
     * @return url
     */
    public function getUrl()
    {
        return env('APP_URL').'/obeko/'.$this->id;
    }

    /**
     * Get cover url.
     *
     * @param string $size
     *
     * @return cover_url
     */
    public function getCoverUrl($size)
    {
        $cover_file_name = '';

        switch ($size) {
            case '480':
                $cover_file_name = $this->cover.'-480.jpg';
                break;
            case '960':
                $cover_file_name = $this->cover.'-960.jpg';
                break;
            default:
                $cover_file_name = $this->cover.'.jpg';
                break;
        }

        $cover_type = 'youtube';
        if ($this->cover_type == 'custom') {
            $cover_type = 'obeko';
        }

        return env('CDN_HOST').'/'.$cover_type.'/'.
                $cover_file_name.'?'.strtotime($this->updated_at);
    }

    /**
     * create obeobeko cover.
     *
     * @param string $image_resource
     *
     * @return obeobeko_cover
     */
    public function createObeObeKoCover($image_resource)
    {
        $obeobeko_cover_name_uuid = Uuid::generate(5, 'obeko_'.$this->id, Uuid::NS_DNS);
        $obeobeko_cover_name = $obeobeko_cover_name_uuid->string;

        $obeobeko_resource = $image_resource;
        $obeobeko_960_resouce = ImageHelper::crop($obeobeko_resource, 960);
        $obeobeko_480_resouce = ImageHelper::crop($obeobeko_resource, 480);

        Storage::disk('obeobeko-public-s3')->put('obeko/'.$obeobeko_cover_name.'.jpg', $obeobeko_resource);
        Storage::disk('obeobeko-public-s3')->put('obeko/'.$obeobeko_cover_name.'-960.jpg', $obeobeko_960_resouce);
        Storage::disk('obeobeko-public-s3')->put('obeko/'.$obeobeko_cover_name.'-480.jpg', $obeobeko_480_resouce);

        return $obeobeko_cover_name;
    }

    /**
     * Get obeobeko like user.
     *
     * @return user
     */
    public function getLikeUser()
    {
        $user_ids = DB::select(
            'SELECT olr.user_id '.
                'FROM obeobeko_like_records olr '.
                'WHERE olr.obeobeko_id = ? '.
                'GROUP BY olr.user_id '.
                'ORDER BY olr.created_at DESC',
            [$this->id]
        );

        $user_ids = array_pluck($user_ids, 'user_id');

        return User::whereIn('id', $user_ids)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get obeobeko play user.
     *
     * @return user
     */
    public function getPlayUser()
    {
        $user_ids = DB::select(
            'SELECT opr.user_id '.
                'FROM obeobeko_play_records opr '.
                'WHERE opr.obeobeko_id = ? '.
                'AND opr.user_id != 0 '.
                'GROUP BY opr.user_id '.
                'ORDER BY opr.created_at DESC',
            [$this->id]
        );

        $user_ids = array_pluck($user_ids, 'user_id');

        return User::whereIn('id', $user_ids)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get obeobeko like num.
     *
     * @return int
     */
    public function getLikeNum()
    {
        return DB::table('obeobeko_like_records')
                ->where('obeobeko_id', $this->id)
                ->count();
    }

    /**
     * Get obeobeko play num.
     *
     * @return int
     */
    public function getPlayNum()
    {
        return DB::table('obeobeko_play_records')
                ->where('obeobeko_id', $this->id)
                ->count();
    }

    /**
     * Get obeobeko comment num.
     *
     * @return int
     */
    public function getCommentNum()
    {
        return DB::table('obeobeko_comments')
                ->where('obeobeko_id', $this->id)
                ->count();
    }

    /**
     * Get obeobeko comments.
     *
     * @return comments
     */
    public function getObeObeKoComments()
    {
        $comments = DB::table('obeobeko_comments')
            ->join('users', 'obeobeko_comments.user_id', '=', 'users.id')
            ->select('users.username', 'obeobeko_comments.id', 'obeobeko_comments.comment')
            ->where('obeobeko_comments.obeobeko_id', $this->id)
            ->orderBy('obeobeko_comments.id')
            ->get();

        $process_comments = [];
        foreach ($comments as $comment) {
            array_push(
                $process_comments,
                (object) [
                    'username' => StringHelper::autoLink('@'.$comment->username),
                    'comment' => StringHelper::autoLink($comment->comment),
                ]
            );
        }

        return $process_comments;
    }

    /**
     * Get conetent auto link.
     *
     * @return auto_link_content
     */
    public function contentAutoLink()
    {
        return StringHelper::autoLink(trim($this->content));
    }

    /**
     * Get conetent strip 50.
     *
     * @return strip_content
     */
    public function contentStrip50()
    {
        if (mb_strlen(trim($this->content), 'utf-8') > 50) {
            $strip_content = mb_substr(trim($this->content), 0, 50, 'utf-8');

            return $strip_content.'...';
        } else {
            return trim($this->content);
        }
    }

    /**
     * Get conetent strip 140.
     *
     * @return strip_content
     */
    public function contentStrip140()
    {
        if (mb_strlen(trim($this->content), 'utf-8') > 140) {
            $strip_content = mb_substr(trim($this->content), 0, 140, 'utf-8');

            return $strip_content.'...';
        } else {
            return trim($this->content);
        }
    }

    /**
     * Get conetent too big.
     *
     * @return bool
     */
    public function contentTooBig()
    {
        $content_length = mb_strlen(trim($this->content), 'utf-8');
        if ($content_length > 140) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get conetent strip too big.
     *
     * @return strip_content
     */
    public function contentStripTooBig()
    {
        if (mb_strlen(trim($this->content), 'utf-8') > 140) {
            $strip_content = mb_substr(trim($this->content), 0, 140, 'utf-8');
            $strip_content = nl2br(htmlspecialchars($strip_content, ENT_QUOTES, 'UTF-8'));

            return $strip_content.
                    ' ... <a class="obeobeko-content-show-more">'.
                        '<span class="text-primary">'.
                            trans('obeobeko.more').
                        '</span>'.
                    '</a>';
        } else {
            return StringHelper::autoLink(trim($this->content));
        }
    }

    /**
     * Get conetent strip.
     *
     * @return strip_content
     */
    public function contentStrip()
    {

        //$strip_content = StringHelper::stripHashTags($this->content);
        $strip_content = htmlspecialchars(trim($this->content), ENT_QUOTES, 'UTF-8');
        if (mb_strlen($strip_content, 'utf-8') > 50) {
            return mb_substr($strip_content, 0, 50, 'utf-8').
                    ' ... <small><span class="text-primary">'.trans('obeobeko.more').'</span></small>';
        } else {
            return $strip_content;
        }
    }

    /**
     * Get section heading class.
     *
     * @return string
     */
    public function getSectionHeadingClass()
    {
        $content_length = mb_strlen(trim($this->content), 'utf-8');
        if ($content_length <= 20) {
            return 'section-heading';
        } elseif ($content_length <= 50) {
            return 'section-heading-30';
        } elseif ($content_length <= 100) {
            return 'section-heading-24';
        } elseif ($content_length <= 200) {
            return 'section-heading-18';
        } elseif ($content_length > 200) {
            return 'section-heading-14';
        } else {
            return 'section-heading';
        }
    }

    /**
     * Get owner next obeobeko.
     *
     * @return obeobeko
     */
    public function getOwnerNextObeobeko()
    {
        return self::where('user_id', $this->user_id)
            ->where('created_at', '<', $this->created_at)
            ->where('content', '!=', '')
            ->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get owner previous obeobeko.
     *
     * @return obeobeko
     */
    public function getOwnerPreviousObeobeko()
    {
        return self::where('user_id', $this->user_id)
            ->where('created_at', '>', $this->created_at)
            ->where('content', '!=', '')
            ->orderBy('created_at', 'asc')->first();
    }

    /**
     * Get human readable created at.
     *
     * @return time_string
     */
    public function getHumanReadableCreatedAt()
    {
        Carbon::setLocale(config('app.locale'));

        return $this->created_at->diffForHumans();
    }
}
