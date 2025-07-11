<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Collection\Collection;

/**
 * Bookmark Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $url
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Tag[] $tags
 * @property string $tag_string
 */
class Bookmark extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'title' => true,
        'description' => true,
        'url' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'tags' => true,
        'tag_string' => true, // ✅ campo virtual permitido
    ];

    /**
     * Getter virtual para exibir as tags como uma string formatada
     *
     * @return string
     */
    protected function _getTagString(): string
    {
        if (isset($this->_fields['tag_string'])) {
            return $this->_fields['tag_string'];
        }

        if (empty($this->tags)) {
            return '';
        }

        $tags = new Collection($this->tags);
        $str = $tags->reduce(function ($string, $tag) {
            return $string . $tag->title . ', ';
        }, '');

        return trim($str, ', ');
    }
}
