<?php defined('_JEXEC') or die; ?>
<?php
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Component\Contact\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentHelperRoute;

$showImage = $params->get('show_image');
$showMisc = $params->get('show_misc');
$showForm = $params->get('show_contact_form');
$showArticles = (bool) $params->get('show_articles');
$articleLimit = (int) $params->get('article_limit', 5);

$data = ModAuthorInfoHelper::getAuthorContact();
if (!$data) return;

$contact = $data->contact;
$articles = $data->articles;
if ($showArticles && is_array($articles)) {
    $articles = array_slice($articles, 0, $articleLimit);
}
?>

<?php if ($customCss = trim($params->get('customCss'))): ?>
    <style>
        <?php echo $customCss; ?>
    </style>
<?php endif; ?>

<?php
$miscOutput = '';
if ($showMisc && $contact->misc) {
    $cleaned = preg_replace('/\s+/', ' ', $contact->misc);
    $parts = preg_split('/<hr[^>]+id=["\']system-readmore["\'][^>]*>/i', $cleaned, 2);
    $intro = $parts[0];
    $rest = $parts[1] ?? '';
    ob_start();
    ?>
    <div class="author-bio">
        <?php echo $intro; ?>
        <?php if (!empty($rest)): ?>
            <p>
                <a href="<?php echo Route::_(RouteHelper::getContactRoute($contact->id, $contact->catid)); ?>">
                    <?php echo sprintf(Text::_('MOD_AUTHORINFO_READ_MORE'), htmlspecialchars($contact->name)); ?>
                </a>
            </p>
        <?php endif; ?>
    </div>
    <?php
    $miscOutput = ob_get_clean();
}
?>

<?php if ($showImage && $contact->image && $miscOutput): ?>
    <div class="author-info-flex">
        <div class="author-photo">
            <img src="<?php echo htmlspecialchars($contact->image); ?>" alt="<?php echo htmlspecialchars($contact->name); ?>" />
        </div>
        <?php echo $miscOutput; ?>
    </div>
<?php elseif ($showImage && $contact->image): ?>
    <div class="author-photo">
        <img src="<?php echo htmlspecialchars($contact->image); ?>" alt="<?php echo htmlspecialchars($contact->name); ?>" />
    </div>
<?php elseif ($miscOutput): ?>
    <?php echo $miscOutput; ?>
<?php endif; ?>

<?php if ($showArticles && !empty($articles)): ?>
    <div class="author-articles">
        <h4><?php echo sprintf(Text::_('MOD_AUTHORINFO_OTHER_ARTICLES'), htmlspecialchars($contact->name)); ?></h4>
        <ul>
            <?php foreach ($articles as $article): ?>
                <li>
                    <a href="<?php echo Route::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language)); ?>">
                        <?php echo htmlspecialchars($article->title); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($showForm): ?>
    <p>
        <a href="<?php echo Route::_(RouteHelper::getContactRoute($contact->id, $contact->catid)); ?>">
            <?php echo sprintf(Text::_('MOD_AUTHORINFO_CONTACT_LINK'), htmlspecialchars($contact->name)); ?>
        </a>
    </p>
<?php endif; ?>
