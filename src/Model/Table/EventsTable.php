<?php
namespace App\Model\Table;

use App\Model\Entity\Event;
use App\Model\Entity\Tag;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Events Model
 *
 * @property UsersTable|BelongsTo $Users
 * @property CategoriesTable|BelongsTo $Categories
 * @property EventSeriesTable|BelongsTo $EventSeries
 * @property ImagesTable|BelongsToMany $Images
 * @property TagsTable|BelongsToMany $Tags
 *
 * @method Event get($primaryKey, $options = [])
 * @method Event newEntity($data = null, array $options = [])
 * @method Event[] newEntities(array $data, array $options = [])
 * @method Event|bool save(EntityInterface $entity, $options = [])
 * @method Event patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Event[] patchEntities($entities, array $data, array $options = [])
 * @method Event findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 * @mixin \Search\Model\Behavior\SearchBehavior
 */
class EventsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('events');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->addBehavior('Search.Search');
        $this->searchManager()
            ->add('q', 'Search.Like', [
                'before' => true,
                'after' => true,
                'fieldMode' => 'OR',
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'field' => ['title', 'description', 'location'],
            ]);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('EventSeries', [
            'foreignKey' => 'series_id',
        ]);
        $this->belongsToMany('Images', [
            'foreignKey' => 'event_id',
            'targetForeignKey' => 'image_id',
            'joinTable' => 'events_images',
            'saveStrategy' => 'replace',
        ]);
        $this->belongsToMany('Tags', [
            'foreignKey' => 'event_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'events_tags',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id');

        $validator
            ->integer('category_id')
            ->requirePresence('category_id', 'create', 'Event category must be selected');

        $validator
            ->scalar('title')
            ->requirePresence('title', 'create')
            ->allowEmptyString('title', 'Event title cannot be empty', false);

        $validator
            ->scalar('description')
            ->requirePresence('description', 'create')
            ->allowEmptyString('description', 'Event description cannot be empty', false);

        $validator
            ->scalar('location')
            ->requirePresence('location', 'create')
            ->allowEmptyString('location', 'Event location cannot be empty', false);

        $validator
            ->scalar('location_details')
            ->allowEmptyString('location_details');

        $validator
            ->scalar('address')
            ->allowEmptyString('address');

        $validator
            ->date('date')
            ->requirePresence('date', 'create')
            ->allowEmptyDate('date', 'Event date must be specified', false);

        $validator
            ->time('time_start')
            ->requirePresence('time_start', 'create')
            ->allowEmptyTime('time_start', 'Event start time must be specified', false);

        $validator
            ->time('time_end')
            ->allowEmptyTime('time_end');

        $validator
            ->scalar('age_restriction')
            ->allowEmptyString('age_restriction');

        $validator
            ->scalar('cost')
            ->allowEmptyString('cost');

        $validator
            ->scalar('source')
            ->allowEmptyString('source');

        $validator
            ->boolean('published')
            ->requirePresence('published', 'create', 'Event published/unpublished status missing');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['approved_by'], 'Users'));
        $rules->add($rules->existsIn(['category_id'], 'Categories'));
        $rules->add($rules->existsIn(['series_id'], 'EventSeries'));

        return $rules;
    }

    /**
     * Applies default parameters to the events query for an API call
     *
     * @param Query $query Query
     * @return Query
     */
    public function findForApi(Query $query)
    {
        $query
            ->find('published')
            ->find('withAllAssociated');

        return $query;
    }

    /**
     * Modifies a query to only return published events
     *
     * @param Query $query Query
     * @return Query
     */
    public function findPublished(Query $query)
    {
        $query->where(['Events.published' => true]);

        return $query;
    }

    /**
     * Limits the query to events on or after the specified date
     *
     * @param Query $query Query
     * @param array $options Array of options, with 'date' expected
     * @return $this|Query
     * @throws InternalErrorException
     * @throws BadRequestException
     */
    public function findStartingOn(Query $query, array $options)
    {
        if (!array_key_exists('date', $options)) {
            throw new InternalErrorException("\$options['date'] unspecified");
        }

        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $options['date'])) {
            throw new BadRequestException('Dates must be in the format YYYY-MM-DD');
        }

        return $query
            ->where([
                function (QueryExpression $exp) use ($options) {
                    return $exp->gte('date', $options['date']);
                },
            ]);
    }

    /**
     * Limits the query to events before or on the specified date
     *
     * Allows 'date' to be null, which leaves the query unaffected
     *
     * @param Query $query Query
     * @param array $options Array of options, with 'date' expected
     * @return $this|Query
     * @throws InternalErrorException
     * @throws BadRequestException
     */
    public function findEndingOn(Query $query, array $options)
    {
        if (!array_key_exists('date', $options)) {
            throw new InternalErrorException("\$options['date'] unspecified");
        }

        if (!$options['date']) {
            return $query;
        }

        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $options['date'])) {
            throw new BadRequestException('Dates must be in the format YYYY-MM-DD');
        }

        return $query
            ->where([
                function (QueryExpression $exp) use ($options) {
                    return $exp->lte('date', $options['date']);
                },
            ]);
    }

    /**
     * Limits the query to events with the supplied tags
     *
     * Allows 'tags' to be null, which leaves the query unaffected
     *
     * @param Query $query Query
     * @param array $options Array of options, with 'tags' expected
     * @return $this|Query
     * @throws InternalErrorException
     * @throws BadRequestException
     */
    public function findTagged(Query $query, array $options)
    {
        if (!array_key_exists('tags', $options)) {
            throw new InternalErrorException("\$options['tags'] unspecified");
        }

        $tags = $options['tags'];
        if (empty($tags)) {
            return $query;
        }

        if (!is_array($tags)) {
            throw new BadRequestException('Tags must be provided as an array');
        }

        $tags = array_map('mb_strtolower', $tags);
        $conditions = [];
        foreach ($tags as $tag) {
            $conditions[] = ['Tags.name' => $tag];
        }

        return $query
            ->leftJoinWith('Tags')
            ->where(['OR' => $conditions]);
    }

    /**
     * Limits the query to events in the specified category
     *
     * @param Query $query Query
     * @param array $options Array of options, with 'categoryId' expected
     * @return $this|Query
     * @throws InternalErrorException
     * @throws BadRequestException
     */
    public function findInCategory(Query $query, array $options)
    {
        if (!array_key_exists('categoryId', $options)) {
            throw new InternalErrorException("\$options['categoryId'] unspecified");
        }

        $categoryId = $options['categoryId'];
        if (empty($categoryId)) {
            return $query;
        }

        return $query->where(['category_id' => $categoryId]);
    }

    /**
     * Limits the query to events on or after today's date
     *
     * @param Query $query Query
     * @return $this|Query
     * @throws InternalErrorException
     * @throws BadRequestException
     */
    public function findFuture(Query $query)
    {
        return $query
            ->where([
                function (QueryExpression $exp) {
                    $timezone = Configure::read('localTimezone');

                    return $exp->gte('date', (new FrozenTime('now', $timezone))->format('Y-m-d'));
                },
            ]);
    }

    /**
     * Limits the query to events before today's date
     *
     * @param Query $query Query
     * @return $this|Query
     * @throws InternalErrorException
     * @throws BadRequestException
     */
    public function findPast(Query $query)
    {
        return $query
            ->where([
                function (QueryExpression $exp) {
                    $timezone = Configure::read('localTimezone');

                    return $exp->lt('date', (new FrozenTime('now', $timezone))->format('Y-m-d'));
                },
            ]);
    }

    /**
     * Orders the query by date and time
     *
     * Orders with increasing dates and times by default, but ['direction' => 'DESC'] will sort by decreasing dates and
     * increasing times (such as when viewing past events from the most recent to the most... uh... past. Most past.
     *
     * @param Query $query Query
     * @param array $options Array of options, notably 'direction'
     * @return Query
     */
    public function findOrdered(Query $query, array $options)
    {
        $direction = $options['direction'] ?? 'ASC';
        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            throw new InternalErrorException('Unrecognized ordering direction: ' . $direction);
        }

        return $query->order([
            'date' => $direction,
            'time_start' => 'ASC',
        ]);
    }

    /**
     * Returns the count of upcoming events in the specified category
     *
     * @param int $categoryId Category ID
     * @return int
     */
    public function getCategoryUpcomingEventCount($categoryId)
    {
        return $this
            ->find('future')
            ->find('inCategory', ['categoryId' => $categoryId])
            ->count();
    }

    /**
     * Returns an alphabetized array of tags associated with upcoming published events,
     * plus the count of how many events each is associated with
     *
     * @param string $direction Direction to search, either 'future' or 'past'
     * @param int $categoryId ID of a category record
     * @return Tag[]
     * @throws InternalErrorException
     */
    public function getEventTags($direction = 'future', $categoryId = null)
    {
        if (!in_array($direction, ['future', 'past'])) {
            throw new InternalErrorException('Invalid direction: ' . $direction);
        }

        $query = $this->find($direction)
            ->select(['id'])
            ->where(['published' => true])
            ->contain([
                'Tags' => function (Query $query) {
                    return $query->select(['id', 'name']);
                },
            ]);
        if ($categoryId) {
            $query->where(['category_id' => $categoryId]);
        }

        $events = $query->all();

        $tags = [];
        foreach ($events as $event) {
            foreach ($event->tags as $tag) {
                if (isset($tags[$tag->name])) {
                    $tags[$tag->name]->count++;
                    continue;
                }
                $tag->count = 1;
                $tags[$tag->name] = $tag;
            }
        }

        ksort($tags);

        return $tags;
    }

    /**
     * Limits a query to events in a specified month
     *
     * @param Query $query Query object
     * @param array $options Options array
     * @return Query
     * @throws InternalErrorException
     */
    public function findInMonth(Query $query, $options)
    {
        if (!isset($options['month'])) {
            throw new InternalErrorException('Month parameter missing');
        }
        if (!isset($options['year'])) {
            throw new InternalErrorException('Year parameter missing');
        }
        $month = $options['month'];
        $year = $options['year'];

        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $query
            ->where(function (QueryExpression $exp) use ($year, $month) {
                return $exp->like('date', "$year-$month-%");
            })
            ->limit(31);

        return $query;
    }

    /**
     * Returns a query modified to contain() all models that the Event model is associated with
     *
     * @param Query $query Query object
     * @return Query
     */
    public function findWithAllAssociated(Query $query)
    {
        return $query
            ->contain([
                'Users',
                'Categories',
                'EventSeries',
                'Images',
                'Tags',
            ]);
    }

    /**
     * Returns a query to collect the information needed for the event moderation page
     *
     * @param Query $query Query object
     * @return Query
     */
    public function findForModeration(Query $query)
    {
        return $query
            ->find('withAllAssociated')
            ->contain([
                'EventSeries' => function (Query $q) {
                    return $q
                        ->select(['id', 'title'])
                        ->contain([
                            'Events' => function (Query $q) {
                                return $q->select(['id', 'series_id']);
                            },
                        ]);
                },
            ])
            ->orderAsc('Events.created')
            ->where([
                'OR' => [
                    function (QueryExpression $exp) {
                        return $exp->isNull('Events.approved_by');
                    },
                    ['Events.published' => '0'],
                ],
            ]);
    }

    /**
     * Returns an array of dates (YYYY-MM-DD) with published events, cached daily
     *
     * @param string|int|null $month Month (integer values from 1-12 or string values from '01' to '12')
     * @param int|null $year Four-digit year
     * @return array
     * @throws InternalErrorException
     */
    public function getPopulatedDates($month = null, $year = null)
    {
        if ($year xor $month) {
            throw new InternalErrorException('Both month and year need to be specified to find events in month');
        }
        if ($month) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        }
        $cacheKey = sprintf(
            'populated-dates%s%s',
            $year ? "-$year" : '',
            $month ? "-$month" : ''
        );

        return Cache::remember($cacheKey, function () use ($month, $year) {
            $query = $this->find()
                ->select(['date'])
                ->distinct('date')
                ->where([
                    'Events.published' => true,
                    function (QueryExpression $exp) {
                        return $exp->isNotNull('date');
                    },
                ])
                ->orderAsc('date')
                ->enableHydration(false);

            // Apply optional month/year limits
            if ($month && $year) {
                $query->find('inMonth', compact('month', 'year'));
            }

            $dates = [];
            foreach ($query->all() as $result) {
                /** @var FrozenDate $date */
                $date = $result['date'];
                $dates[] = $date->format('Y-m-d');
            }

            return $dates;
        }, 'daily');
    }

    /**
     * Returns the number of currently unapproved events
     *
     * @return int
     */
    public function getUnapprovedCount()
    {
        return $this
            ->find()
            ->where(function (QueryExpression $exp) {
                return $exp->isNull('approved_by');
            })
            ->count();
    }

    /**
     * Finds events at a location, specified by name or slug
     *
     * If 'location_slug' is provided, note that this might capture events at locations with different names that share
     * that same slug, due to capitalization and punctuation variations and there being no mechanism for enforcing slug
     * uniqueness. For example, /locations/i-m-foo would look for events at locations called "I'm Foo", "I-M Foo",
     * and "i m foo".
     *
     * @param Query $query Query
     * @param array $options Array of options, with 'location' expected
     * @return Query
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function findAtLocation(Query $query, array $options)
    {
        if (!array_key_exists('location', $options) && !array_key_exists('location_slug', $options)) {
            throw new InternalErrorException('Either \'location\' or \'location_slug\' must be specified');
        }

        if (array_key_exists('location', $options)) {
            $query->where(['location' => $options['location']]);
        }
        if (array_key_exists('location_slug', $options)) {
            $query->where(['location_slug' => $options['location_slug']]);
        }

        return $query;
    }

    /**
     * Takes a location name slug and returns the full name of a location
     *
     * Pulls this full location name from an arbitrarily chosen event. Note that there may be different location names
     * that share the same slug, due to variations in capitalization and punctuation. This method will choose one
     * arbitrarily to return.
     *
     * @param string $locationSlug Location name slug
     * @return string
     */
    public function getFullLocationName(string $locationSlug)
    {
        /** @var Event $event */
        $event = $this->find()
            ->select(['location'])
            ->where(['location_slug' => $locationSlug])
            ->first();

        return $event ? $event->location : '';
    }

    /**
     * A custom general finder for searching for events by an arbitrary string
     *
     * Takes a 'q' search term and modifies the query to return events with searchable fields or associated models
     * containing that term, which could be in a title, description, or tag.
     *
     * @param Query $query Query
     * @param array $options Array of options, with 'q' expected
     * @return Query
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function findBySearchableFields(Query $query, array $options)
    {
        if (!array_key_exists('q', $options)) {
            throw new InternalErrorException('\'q\' search term not provided');
        }

        $searchTerm = $options['q'];
        $query->where([
            'OR' => [
                function (QueryExpression $exp) use ($searchTerm) {
                    return $exp->like('Events.title', "%$searchTerm%");
                },
                function (QueryExpression $exp) use ($searchTerm) {
                    $exp->like('Events.location', "%$searchTerm%");
                },
                function (QueryExpression $exp) use ($searchTerm) {
                    $exp->like('Events.description', "%$searchTerm%");
                },
            ],
        ]);

        return $query;
    }

    /**
     * Returns a Query for event search results
     *
     * Making this a custom finder resulted in "maximum function nesting level reached" errors, suggesting that finders
     * cannot be nested.
     *
     * @param string $searchTerm An arbitrary string to search for
     * @param string $direction Either 'future', 'past', or 'all' (ignored) expected
     * @return Query
     */
    public function getSearchResultsQuery($searchTerm, $direction)
    {
        $baseQuery = $this
            ->find('published')
            ->find('withAllAssociated');
        if (in_array($direction, ['future', 'past'])) {
            $baseQuery->find($direction);
        }
        $fieldsQuery = (clone $baseQuery)->find('search', ['search' => ['q' => $searchTerm]]);
        $tagsQuery = (clone $baseQuery)->cleanCopy()->find('tagged', ['tags' => [mb_strtolower($searchTerm)]]);

        return $fieldsQuery->union($tagsQuery);
    }

    /**
     * forFeedWidget custom finder
     *
     * @param Query $query Query
     * @param array $options 'filters' required, 'startDate' optional, in YYYY-MM-DD format
     * @return Query
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function findForFeedWidget(Query $query, $options)
    {
        if (!isset($options['filters'])) {
            throw new InternalErrorException('filters not passed to find(\'forFeedWidget\')');
        }

        $timezone = Configure::read('localTimezone');
        $defaultStartDate = (new FrozenTime('now', $timezone))->format('Y-m-d');
        $startDate = $options['startDate'] ?? $defaultStartDate;
        $filters = $options['filters'];
        $datesPerPage = 7;
        $dates = $this->getNextPopulatedDays($startDate, $datesPerPage, $filters);

        // There are no more populated dates, return a simple query that won't return any events
        if (!$dates) {
            return $query->where(['id' => -1]);
        }

        $query
            ->find('published')
            ->find('ordered')
            ->find('filteredForWidget', ['filters' => $filters])
            ->select([
                'id',
                'title',
                'location',
                'date',
                'time_start',
            ])
            ->where(function (QueryExpression $exp) use ($dates) {
                return $exp->in('date', $dates);
            })
            ->contain([
                'Categories' => function (Query $q) {
                    return $q->select(['id', 'name', 'slug']);
                },
                'Images',
            ]);

        return $query;
    }

    /**
     * Returns the next $limit YYYY-MM-DD dates with events on and after $startDate, filtered by $filters
     *
     * @param string $startDate The earliest date that can be returned
     * @param int $limit Limit of dates to retrieve
     * @param array $filters Array of filters used by Widgets
     * @return string[]
     */
    private function getNextPopulatedDays($startDate, $limit, $filters = [])
    {
        $results = $this
            ->find('published')
            ->find('filteredForWidget', ['filters' => $filters])
            ->select(['date'])
            ->distinct('date')
            ->where([
                function (QueryExpression $exp) use ($startDate) {
                    return $exp->gte('date', $startDate);
                },
            ])
            ->limit($limit)
            ->orderAsc('date')
            ->toArray();

        return Hash::extract($results, '{n}.date');
    }

    /**
     * Modifies a query to apply $options['filters'] to it
     *
     * @param Query $query Query
     * @param array $options 'filters' required
     * @return Query
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function findFilteredForWidget(Query $query, $options)
    {
        $filters = $options['filters'];
        $categoryFilter = $filters['category'] ?? null;
        if ($categoryFilter) {
            $query->where(function (QueryExpression $exp) use ($categoryFilter) {
                if (is_int($categoryFilter)) {
                    $categoryFilter = [$categoryFilter];
                }

                return $exp->in('category_id', $categoryFilter);
            });
        }

        $locationFilter = $filters['location'] ?? null;
        if ($locationFilter) {
            $query->where(function (QueryExpression $exp) use ($locationFilter) {
                return $exp->like('location', "%$locationFilter%");
            });
        }

        /* If there are included/excluded tags,
         * retrieve all potentially applicable event IDs that must / must not be part of the final results */
        $eventIds = [];
        foreach (['included', 'excluded'] as $foocluded) {
            if (!isset($filters["tags_$foocluded"])) {
                continue;
            }
            $tagIds = $filters["tags_$foocluded"];
            $eventIds[$foocluded] = $this->Tags->getFilteredAssociatedEventIds(
                $tagIds,
                $categoryFilter,
                $locationFilter
            );
        }
        if (isset($eventIds['included'])) {
            $query->where(function (QueryExpression $exp) use ($eventIds) {
                if ($eventIds['included']) {
                    return $exp->in('Events.id', $eventIds['included']);
                }

                /* If no event IDs are to be included, then add an impossible condition because
                 * "event ID in (empty set)" will generate an error */
                return $exp->lt('Events.id', 0);
            });
        }
        if (isset($eventIds['excluded']) && $eventIds['excluded']) {
            $query->where(function (QueryExpression $exp) use ($eventIds) {
                return $exp->notIn('Events.id', $eventIds['excluded']);
            });
        }

        return $query;
    }

    /**
     * forMonthWidget custom finder
     *
     * @param Query $query Query
     * @param array $options 'filters', 'year', and 'month required
     * @return Query
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function findForMonthWidget(Query $query, $options)
    {
        foreach (['filters', 'year', 'month'] as $requiredParam) {
            if (!isset($options[$requiredParam])) {
                throw new InternalErrorException($requiredParam . ' not passed to find(\'forMonthWidget\')');
            }
        }

        $query
            ->find('inMonth', compact('year', 'month'))
            ->find('published')
            ->find('ordered')
            ->find('filteredForWidget', ['filters' => $options['filters']])
            ->select([
                'id',
                'title',
                'location',
                'date',
                'time_start',
            ])
            ->contain([
                'Categories' => function (Query $q) {
                    return $q->select(['id', 'name', 'slug']);
                },
                'Images',
            ]);

        return $query;
    }

    /**
     * Modifies a query to only return events taking place today and in the six days that follow
     *
     * @param Query $query Query
     * @return Query
     */
    public function findUpcomingWeek(Query $query)
    {
        $timezone = Configure::read('localTimezone');
        $startingDate = (new FrozenTime('now', $timezone))->format('Y-m-d');
        $endingDate = (new FrozenTime('now + 6 days', $timezone))->format('Y-m-d');

        $query
            ->find('startingOn', ['date' => $startingDate])
            ->find('endingOn', ['date' => $endingDate]);

        return $query;
    }

    /**
     * Limits the query to events on the specified date
     *
     * @param Query $query Query
     * @param array $options Array of options, with 'date' expected
     * @return $this|Query
     * @throws InternalErrorException
     * @throws BadRequestException
     */
    public function findOn(Query $query, array $options)
    {
        return $query
            ->find('startingOn', ['date' => $options['date']])
            ->find('endingOn', ['date' => $options['date']]);
    }
}
