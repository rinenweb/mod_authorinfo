<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper as ContactRouteHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentRouteHelper;

$e = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$contact  = $authorData->contact;
$articles = $authorData->articles;

$showImage  = (bool) $params->get('show_image', 1) && !empty($contact->image);
$showMisc   = (bool) $params->get('show_misc', 1);
$showLink   = (bool) $params->get('show_contact_form', 0);
$headingTag = $params->get('heading_tag', 'h4');
$headingTag = in_array($headingTag, ['h2', 'h3', 'h4', 'h5', 'h6', 'p'], true) ? $headingTag : 'h4';

$contactUrl = Route::_(ContactRouteHelper::getContactRoute(
    $contact->id . ':' . $contact->alias,
    $contact->catid,
    $contact->language
));

// Custom CSS goes into <head> via the Web Asset Manager
$customCss = trim((string) $params->get('customCss', ''));

if ($customCss !== '') {
    Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineStyle($customCss);
}

// Split the bio at the "Read more" separator, then run content plugins on the visible part
$intro = '';
$rest  = '';

if ($showMisc && !empty($contact->misc)) {
    $parts = preg_split('/<hr\s[^>]*id=["\']system-readmore["\'][^>]*>/i', $contact->misc, 2);
    $intro = trim(HTMLHelper::_('content.prepare', $parts[0], '', 'mod_authorinfo.misc'));
    $rest  = trim($parts[1] ?? '');
}

$both = $showImage && $intro !== '';
?>
<div class="mod-authorinfo">
    <?php if ($both) : ?><div class="author-info-flex"><?php endif; ?>

    <?php if ($showImage) : ?>
        <div class="author-photo">
            <?php echo LayoutHelper::render('joomla.html.image', ['src' => $contact->image, 'alt' => $contact->name]); ?>
        </div>
    <?php endif; ?>

    <?php if ($intro !== '') : ?>
        <div class="author-bio">
            <?php echo $intro; ?>
            <?php if ($rest !== '') : ?>
                <p>
                    <a href="<?php echo $contactUrl; ?>">
                        <?php echo Text::sprintf('MOD_AUTHORINFO_READ_MORE', $e($contact->name)); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($both) : ?></div><?php endif; ?>

    <?php if (!empty($articles)) : ?>
        <div class="author-articles">
            <<?php echo $headingTag; ?>>
                <?php echo Text::sprintf('MOD_AUTHORINFO_OTHER_ARTICLES', $e($contact->name)); ?>
            </<?php echo $headingTag; ?>>
            <ul>
                <?php foreach ($articles as $item) : ?>
                    <li>
                        <a href="<?php echo Route::_(ContentRouteHelper::getArticleRoute($item->slug, $item->catid, $item->language)); ?>">
                            <?php echo $e($item->title); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($showLink) : ?>
        <p class="author-contact-link">
            <a href="<?php echo $contactUrl; ?>">
                <?php echo Text::sprintf('MOD_AUTHORINFO_CONTACT_LINK', $e($contact->name)); ?>
            </a>
        </p>
    <?php endif; ?>
</div>
