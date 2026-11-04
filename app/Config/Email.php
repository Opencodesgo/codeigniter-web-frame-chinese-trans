<?php
/**
 * 配置，电子邮件
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
	/**
	 * @var string
	 */
	public $fromEmail;

	/**
	 * @var string
	 */
	public $fromName;

	/**
	 * @var string
	 */
	public $recipients;

	/**
	 * The "user agent"
	 * “用户代理”
	 *
	 * @var string
	 */
	public $userAgent = 'CodeIgniter';

	/**
	 * The mail sending protocol: mail, sendmail, smtp
	 * 邮件发送协议：mail、sendmail、smtp
	 *
	 * @var string
	 */
	public $protocol = 'mail';

	/**
	 * The server path to Sendmail.
	 * Sendmail的服务器路径
	 *
	 * @var string
	 */
	public $mailPath = '/usr/sbin/sendmail';

	/**
	 * SMTP Server Address
	 * SMTP服务器地址
	 *
	 * @var string
	 */
	public $SMTPHost;

	/**
	 * SMTP Username
	 * SMTP用户名
	 *
	 * @var string
	 */
	public $SMTPUser;

	/**
	 * SMTP Password
	 * SMTP密码
	 *
	 * @var string
	 */
	public $SMTPPass;

	/**
	 * SMTP Port
	 * SMTP端口
	 *
	 * @var integer
	 */
	public $SMTPPort = 25;

	/**
	 * SMTP Timeout (in seconds)
	 * SMTP超时时间（秒）
	 *
	 * @var integer
	 */
	public $SMTPTimeout = 5;

	/**
	 * Enable persistent SMTP connections
	 * 启用持久SMTP连接
	 *
	 * @var boolean
	 */
	public $SMTPKeepAlive = false;

	/**
	 * SMTP Encryption. Either tls or ssl
	 * SMTP加密。tls或ssl。
	 *
	 * @var string
	 */
	public $SMTPCrypto = 'tls';

	/**
	 * Enable word-wrap
	 * 启用自动换行
	 *
	 * @var boolean
	 */
	public $wordWrap = true;

	/**
	 * Character count to wrap at
	 * 要换行的字符数
	 *
	 * @var integer
	 */
	public $wrapChars = 76;

	/**
	 * Type of mail, either 'text' or 'html'
	 * 类型的邮件,无论是“文本”还是“html”
	 *
	 * @var string
	 */
	public $mailType = 'text';

	/**
	 * Character set (utf-8, iso-8859-1, etc.)
	 *
	 * @var string
	 */
	public $charset = 'UTF-8';

	/**
	 * Whether to validate the email address
	 * 是否验证邮件地址
	 *
	 * @var boolean
	 */
	public $validate = false;

	/**
	 * Email Priority. 1 = highest. 5 = lowest. 3 = normal
	 *
	 * @var integer
	 */
	public $priority = 3;

	/**
	 * Newline character. (Use “\r\n” to comply with RFC 822)
	 *
	 * @var string
	 */
	public $CRLF = "\r\n";

	/**
	 * Newline character. (Use “\r\n” to comply with RFC 822)
	 *
	 * @var string
	 */
	public $newline = "\r\n";

	/**
	 * Enable BCC Batch Mode.
	 * 启用BCC批处理模式
	 *
	 * @var boolean
	 */
	public $BCCBatchMode = false;

	/**
	 * Number of emails in each BCC batch
	 * 每批密件中的电子邮件数量
	 *
	 * @var integer
	 */
	public $BCCBatchSize = 200;

	/**
	 * Enable notify message from server
	 * 启用来自服务器的通知消息
	 *
	 * @var boolean
	 */
	public $DSN = false;

}
