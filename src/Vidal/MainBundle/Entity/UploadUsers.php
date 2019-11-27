<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Iphp\FileStoreBundle\Mapping\Annotation as FileStore;

/**
 * @ORM\Entity(repositoryClass="Vidal\MainBundle\Entity\UploadUsersRepository") @ORM\Table(name="upload_users")
 * @FileStore\Uploadable
 */
class UploadUsers extends BaseEntity
{
    /**
     * @ORM\Column(type="array", nullable=true)
     * @FileStore\UploadableField(mapping="users")
     * @Assert\NotBlank(message="Необходимо загрузить файл excel с участниками")
     */
    protected $file;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $total;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $new;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $newIds;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $old;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $noSpecialty;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $noCity;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $noCityTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $noSpecialtyTotal;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $skipFirstLine = true;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $fields;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $raw;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $deliveryId;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $preview = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $status = UploadUsers::STATUS_NEW;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $error;

    const STATUS_NEW = 'new';
    const STATUS_LOADING = 'loading';
    const STATUS_LOADED = 'loaded';
    const STATUS_PROCESSING = 'processing';
    const STATUS_FINISHED = 'finished';

    const FIELD_FIO = 'фио';
    const FIELD_ORG = 'организация';
    const FIELD_CITY = 'город';
    const FIELD_JOB = 'должность';
    const FIELD_SPEC = 'специальность';
    const FIELD_SPEC2 = 'вторая_специальность';
    const FIELD_PHONE = 'телефон';
    const FIELD_EMAIL = 'email';

    public function __construct()
    {
        $this->fields = implode(', ', self::getAllFields());
    }

    public static function getAllFields()
    {
        return array(
            self::FIELD_FIO => self::FIELD_FIO,
            self::FIELD_ORG => self::FIELD_ORG,
            self::FIELD_CITY => self::FIELD_CITY,
            self::FIELD_JOB => self::FIELD_JOB,
            self::FIELD_SPEC => self::FIELD_SPEC,
            self::FIELD_SPEC2 => self::FIELD_SPEC2,
            self::FIELD_PHONE => self::FIELD_PHONE,
            self::FIELD_EMAIL => self::FIELD_EMAIL,
        );
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_LOADING => 'Участники загружаются',
            self::STATUS_LOADED => 'Участники загружены',
            self::STATUS_PROCESSING => 'Идет рассылка',
            self::STATUS_FINISHED => 'Разослан',
        ];
    }

    public function getStatusLabel()
    {
        $statuses = self::getStatuses();
        $status = $this->getStatus();

        return isset($statuses[$status]) ? $statuses[$status] : null;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    public function addTotal()
    {
        $this->total = $this->total + 1;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getNew()
    {
        return $this->new;
    }

    public function addNew()
    {
        return $this->new = $this->new + 1;
    }

    /**
     * @param mixed $new
     */
    public function setNew($new)
    {
        $this->new = $new;
    }

    /**
     * @return mixed
     */
    public function getNewIds()
    {
        return $this->newIds;
    }

    /**
     * @param mixed $newIds
     */
    public function setNewIds($newIds)
    {
        $this->newIds = $newIds;
    }

    /**
     * @return mixed
     */
    public function getOld()
    {
        return $this->old;
    }

    public function addOld()
    {
        $this->old = $this->old + 1;
    }

    /**
     * @param mixed $old
     */
    public function setOld($old)
    {
        $this->old = $old;
    }

    /**
     * @return mixed
     */
    public function getNoSpecialty()
    {
        return $this->noSpecialty;
    }

    /**
     * @param mixed $noSpecialty
     */
    public function setNoSpecialty($noSpecialty)
    {
        $this->noSpecialty = $noSpecialty;
    }

    /**
     * @return mixed
     */
    public function getNoCity()
    {
        return $this->noCity;
    }

    /**
     * @param mixed $noCity
     */
    public function setNoCity($noCity)
    {
        $this->noCity = $noCity;
    }

    /**
     * @return mixed
     */
    public function getNoCityTotal()
    {
        return $this->noCityTotal;
    }

    public function addNoCityTotal()
    {
        $this->noCityTotal = $this->noCityTotal + 1;
    }

    /**
     * @param mixed $noCityTotal
     */
    public function setNoCityTotal($noCityTotal)
    {
        $this->noCityTotal = $noCityTotal;
    }

    /**
     * @return mixed
     */
    public function getNoSpecialtyTotal()
    {
        return $this->noSpecialtyTotal;
    }

    public function addNoSpecialtyTotal()
    {
        $this->noSpecialtyTotal = $this->noSpecialtyTotal + 1;
    }

    /**
     * @param mixed $noSpecialtyTotal
     */
    public function setNoSpecialtyTotal($noSpecialtyTotal)
    {
        $this->noSpecialtyTotal = $noSpecialtyTotal;
    }

    /**
     * @return mixed
     */
    public function getSkipFirstLine()
    {
        return $this->skipFirstLine;
    }

    /**
     * @param mixed $skipFirstLine
     */
    public function setSkipFirstLine($skipFirstLine)
    {
        $this->skipFirstLine = $skipFirstLine;
    }

    /**
     * @return mixed
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @param mixed $raw
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;
    }

    /**
     * @param mixed $raw
     */
    public function setRawEncode($rows)
    {
        $this->raw = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return mixed
     */
    public function getRawDecode()
    {
        return json_decode($this->raw, true, 512, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return mixed
     */
    public function getFieldsSplitted()
    {
        $fields = array();

        if (!empty($this->fields)) {
            $rawFields = explode(',', $this->fields);
            foreach ($rawFields as $field) {
                $fields[] = trim($field);
            }
        }

        return $fields;
    }

    /**
     * @param mixed $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return mixed
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param mixed $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    public function updateDeliveryId()
    {
        $uploadDate = $this->getCreated()->format('d.m.Y_H:i:s');
        $this->deliveryId = 'Autoregister_' . $uploadDate;
    }

    /**
     * @return mixed
     */
    public function getDeliveryId()
    {
        return $this->deliveryId;
    }

    /**
     * @param mixed $deliveryId
     */
    public function setDeliveryId($deliveryId)
    {
        $this->deliveryId = $deliveryId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }
}