<?php
/**
 * Copyright (c) 2016 AlexaCRM.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Lesser Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AlexaCRM\CRMToolkit;

/**
 * Interface CacheInterface defines an interface for SDK to perform caching of CRM data
 */
interface CacheInterface extends StorageInterface {

    /**
     * Retrieves a value from cache by key
     *
     * @param string $key Cache item key
     * @param mixed $default Default value if not found
     *
     * @return mixed
     */
    public function get( $key, $default = null );

    /**
     * Saves a value in cache by key
     *
     * @param string $key Cache item key
     * @param mixed $value Cache item value
     * @param int $expiresAfter
     *
     * @return void
     */
    public function set( $key, $value, $expiresAfter = null );

    /**
     * Checks whether given cache key exists and is valid
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists( $key );

    /**
     * Purges cache storage
     *
     * This may be performed for the SDK only if proper tagging is
     * implemented by the consuming software.
     *
     * @return void
     */
    public function cleanup();

}
