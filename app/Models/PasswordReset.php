<?php
/**
 * Password_reset Model : 用于存储密码重置令牌,当用户触发密码重置时,将会创建一个新的记录在此表和电子邮件密码重置的URL(包含令牌)将被发送到用户。
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    public $timestamps = false;

}
