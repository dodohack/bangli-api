<?php
/**
 * Customizer user provider, just add a function to retrieve user by UUID
 */
namespace App\Providers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class JwtUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their uuid.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByUuid($identifier)
    {
        return $this->createModel()->newQuery()->where('uuid', $identifier)->first();
    }

    /**
     * Retrieve a user by their uuid, overwrite default method which
     * retrieves user by id.
     *
     * @param mixed $identififer
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */

    public function retrieveById($identififer)
    {
        return $this->retrieveByUuid($identififer);
    }
}
