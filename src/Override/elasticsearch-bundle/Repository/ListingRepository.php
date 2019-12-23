<?php

/**
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Override\ElasticsearchBundle\Repository;

use Cocorico\ElasticsearchBundle\Indexer\BaseIndexer;
use Cocorico\ElasticsearchBundle\Repository\BaseRepository;
use Cocorico\ElasticsearchBundle\Repository\RepositoryInterface;
use Elastica\Query;

class ListingRepository extends BaseRepository
{
    /**
     * @param array $keywords
     * @param null $loc
     * @return array
     */
    public function findByKeywords($keywords)
    {
        $query = new Query();
        foreach ($keywords as $keyword) {
            if (strlen($keyword) < 3) {
                continue;
            }
            $keywordQuery = new Query\Bool();
            foreach ($this->indexer->getFields() as $field) {
                switch ($this->indexer->getFieldType($field)) {
                    case BaseIndexer::TYPE_TRANSLATABLE_TEXT:
                        foreach ($this->locales as $locale) {
                            $fieldQuery = new Query\MatchPhrasePrefix();
                            $realFieldName = sprintf('%s_%s', $field, $locale);
                            $fieldQuery->setFieldAnalyzer($realFieldName, sprintf('text_%s', $locale));
                            $fieldQuery->setFieldBoost($realFieldName, $this->indexer->getFieldBoost($field));
                            $fieldQuery->setFieldQuery($realFieldName, $keyword);
                            $keywordQuery->addShould($fieldQuery);
                        }
                        break;
                }
            }
            $query->setQuery($keywordQuery);
        }
        if ($query->count() === 0) {
            return [];
        }
        return $this->finder->find($query, $this->config['limit']);
    }
}
