<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

class ModAuthorInfoHelper
{
    public static function getAuthorContact(Registry $params)
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        if ($input->getCmd('option') !== 'com_content' || $input->getCmd('view') !== 'article') {
            return null;
        }

        $articleId = $input->getInt('id');

        if (!$articleId) {
            return null;
        }

        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $groups = $app->getIdentity()->getAuthorisedViewLevels();
        $now    = Factory::getDate()->toSql();
        $langs  = Multilanguage::isEnabled() ? [$app->getLanguage()->getTag(), '*'] : [];

        // 1. Who wrote the current article?
        $query = $db->getQuery(true)
            ->select($db->quoteName('created_by'))
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $articleId, ParameterType::INTEGER);

        $authorId = (int) $db->setQuery($query)->loadResult();

        if (!$authorId) {
            return null;
        }

        // 2. The author's contact, respecting access, dates and language
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__contact_details'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('published') . ' = 1')
            ->whereIn($db->quoteName('access'), $groups)
            ->where('(' . $db->quoteName('publish_up') . ' IS NULL OR ' . $db->quoteName('publish_up') . ' <= :now1)')
            ->where('(' . $db->quoteName('publish_down') . ' IS NULL OR ' . $db->quoteName('publish_down') . ' > :now2)')
            ->bind(':userId', $authorId, ParameterType::INTEGER)
            ->bind([':now1', ':now2'], $now)
            // '*' sorts before language codes, so DESC prefers an exact-language contact over "All"
            ->order($db->quoteName('language') . ' DESC')
            ->order($db->quoteName('id') . ' ASC')
            ->setLimit(1);

        if ($langs) {
            $query->whereIn($db->quoteName('language'), $langs, ParameterType::STRING);
        }

        $contact = $db->setQuery($query)->loadObject();

        if (!$contact) {
            return null;
        }

        // 3. Other articles, only if the option is on
        $articles = [];

        if ($params->get('show_articles', 1)) {
            $limit = (int) $params->get('article_limit', 5);

            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'title', 'alias', 'catid', 'language']))
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('state') . ' = 1')
                ->where($db->quoteName('created_by') . ' = :authorId')
                ->where($db->quoteName('id') . ' != :articleId')
                ->whereIn($db->quoteName('access'), $groups)
                ->where('(' . $db->quoteName('publish_up') . ' IS NULL OR ' . $db->quoteName('publish_up') . ' <= :now1)')
                ->where('(' . $db->quoteName('publish_down') . ' IS NULL OR ' . $db->quoteName('publish_down') . ' > :now2)')
                ->bind(':authorId', $authorId, ParameterType::INTEGER)
                ->bind(':articleId', $articleId, ParameterType::INTEGER)
                ->bind([':now1', ':now2'], $now)
                ->order($db->quoteName('created') . ' DESC');

            if ($langs) {
                $query->whereIn($db->quoteName('language'), $langs, ParameterType::STRING);
            }

            if ($limit > 0) {
                $query->setLimit($limit);
            }

            $articles = $db->setQuery($query)->loadObjectList();

            foreach ($articles as $item) {
                $item->slug = $item->id . ':' . $item->alias;
            }
        }

        return (object) [
            'contact'  => $contact,
            'articles' => $articles,
        ];
    }
}
