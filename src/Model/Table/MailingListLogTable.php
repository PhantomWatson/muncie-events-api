<?php
namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Table;

/**
 * @property MailingListTable|BelongsTo $MailingList
 */
class MailingListLogTable extends Table
{
    public const EMAIL_SENT = 0;
    public const ERROR_SENDING = 1;
    public const NO_EVENTS = 2;
    public const NO_APPLICABLE_EVENTS = 3;

    /**
     * Initialize hook method.
     *
     * @param array $config settings for table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('mailing_list_log');
        $this->setDisplayField('result');
        $this->belongsTo('MailingList', [
            'foreignKey' => 'recipient_id',
        ]);
    }

    /**
     * 0: Email sent
     * 1: Error sending email
     * 2: No events today
     * 3: No applicable events today
     * 4: Settings forbid sending email today
     *
     * @param int $recipientId of recipient
     * @param string $result of log
     * @param string $flavor of log
     * @param bool $testing y/n
     * @return EntityInterface|false
     */
    public function addLogEntry($recipientId, $result, $flavor, $testing = false)
    {
        $log = $this->newEntity([]);
        $log->recipient_id = $recipientId;
        $log->flavor = $flavor;
        $log->result = $result;
        $testing = $testing ? 1 : 0;
        $log->testing = $testing;

        return $this->save($log);
    }
}
