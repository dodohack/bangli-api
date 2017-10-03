<?php
/**
 * User Model
 */

namespace App\Models;

use Illuminate\Auth\Authenticatable;
//use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Traits\UserTrait;

class User extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    /* EntrustUserTrait is conflict with Authorizable */
    use Authenticatable, /*Authorizable, */ UserTrait;

    protected $table = 'users';

    // Do not hide this as we need to get these info for backend
    // We are going to manually filter out some entries for frontend use.
    //protected $hidden = ['role_id', 'uuid', 'email', 'created_at', 'updated_at'];

    /**
     * Use UUID as JWT Subject(sub in JWT payload)
     */
    public function getJWTIdentifier()
    {
        return $this->uuid;
    }

    /**
     * Customize JWT Payload (should match auth.bangli.uk)
     */
    public function getJWTCustomClaims()
    {
        return ['iss' => 'bangli.uk',
            'jti' => '0',
            'aud' => $this->name];
    }

    /**
     * Relationships, target tables have FK 'user_id'
     */

    /**
     * A author/editor has many posts
     */
    public function postsByAuthor()
    {
        return $this->hasMany('App\Models\Post', 'author_id');
    }

    public function postsByEditor()
    {
        return $this->hasMany('App\Models\Post', 'editor_id');
    }

    /*
     * A user(editor) has many topics
     */
    public function topics()
    {
        return $this->hasMany('App\Models\Topic', 'editor_id');
    }

    /*
     * A user(editor) has many pages
     */
    public function pages()
    {
        return $this->hasMany('App\Models\Page', 'editor_id');
    }


    /*
     * A user has many revisions(post, page, product)
     */
    public function revisions()
    {
        return $this->hasMany('App\Models\Revision');
    }

    /*
     * A user has multiple addresses
     */
    public function addresses()
    {
        return $this->hasMany('App\Models\UserAddress');
    }

    /*
     * A user can upload many images
     */
    public function images()
    {
        return $this->hasMany('App\Models\Image');
    }

}
