<?php

namespace MainGPT\Page\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\AbstractRenderableAdminAction;
use MainGPT\HookableTrait;

/**
 * This abstract class uniform the behaviour of a class that add an admin page
 */
abstract class AbstractAdminPage extends AbstractRenderableAdminAction
{
    use HookableTrait;

    public const INIT_NAME = 'admin_menu';
}
