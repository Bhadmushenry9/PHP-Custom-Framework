<?php

namespace App\Model;

use Throwable;
use App\Core\Model;

/**
 * @method static array all()
 * @method static array|null find($id)
 * @method static int create(array $data)
 * @method static int update($id, array $data)
 * @method static bool delete($id)
 */
class SignUp extends Model
{
    public function __construct(protected User $userModel, protected Invoice $invoiceModel) {
        parent::__construct();
    }

    public function register(array $userInfo, array $invoiceInfo):string
    {
        try {
            $this->db->beginTransaction();
            $userId = $this->userModel->create($userInfo);
            if (strlen($userId) == 36) {
                $invoiceInfo['user_id'] = $userId;
                $invoiceId = $this->invoiceModel->create($invoiceInfo);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
        return $invoiceId;
    }
}
