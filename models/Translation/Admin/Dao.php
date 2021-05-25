<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Model\Translation\Admin;

use Pimcore\Model;

/**
 * @property \Pimcore\Model\Translation\Admin $model
 *
 * @deprecated
 */
class Dao extends Model\Translation\AbstractTranslation\Dao
{
    /**
     * @var string
     */
    public static $_tableName = 'translations_admin';
}