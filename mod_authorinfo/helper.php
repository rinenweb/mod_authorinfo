<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

class ModAuthorInfoHelper
{
    public static function getAuthorContact()
    {
        $app = Factory::getApplication();
        $input = $app->input;

        if ($input->getCmd('option') !== 'com_content' || $input->getCmd('view') !== 'article') {
            return null;
        }

        $articleId = $input->getInt('id');
        if (!$articleId) return null;

        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('id, created_by')
            ->from('#__content')
            ->where('id = ' . $db->quote($articleId));
        $db->setQuery($query);
        $article = $db->loadObject();

        if (!$article) return null;

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__contact_details')
            ->where('user_id = ' . (int) $article->created_by)
            ->where('published = 1');
        $db->setQuery($query);
        $contact = $db->loadObject();

        if (!$contact) return null;

        $query = $db->getQuery(true)
            ->select('id, title')
            ->from('#__content')
            ->where('state = 1')
            ->where('created_by = ' . (int) $article->created_by)
            ->where('id != ' . $db->quote($articleId))
            ->order('created DESC')
            ->setLimit((int) $limit);
        $db->setQuery($query);
        $otherArticles = $db->loadObjectList();

        return (object) [
            'contact' => $contact,
            'articles' => $otherArticles
        ];
    }
}
