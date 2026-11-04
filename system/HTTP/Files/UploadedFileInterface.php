<?php
/**
 * CodeIgniter，HTTP，文件，上传文件接口
 */

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP\Files;

use InvalidArgumentException;
use RuntimeException;

/**
 * Value object representing a single file uploaded through an
 * HTTP request. Used by the IncomingRequest class to
 * provide files.
 * 表示通过HTTP请求上传的单个文件的值对象。由IncomingRequest类用于提供文件。
 *
 * Typically, implementors will extend the SplFileInfo class.
 * 通常，实现者将扩展SplFileInfo类。
 */
interface UploadedFileInterface
{
	/**
	 * Accepts the file information as would be filled in from the $_FILES array.
	 * 接受将从$_FILES数组中填充的文件信息
	 *
	 * @param string  $path         The temporary location of the uploaded file.
	 * @param string  $originalName The client-provided filename.
	 * @param string  $mimeType     The type of file as provided by PHP
	 * @param integer $size         The size of the file, in bytes
	 * @param integer $error        The error constant of the upload (one of PHP's UPLOADERRXXX constants)
	 */
	public function __construct(string $path, string $originalName, string $mimeType = null, int $size = null, int $error = null);

	//--------------------------------------------------------------------

	/**
	 * Move the uploaded file to a new location.
	 * 将上传的文件移动到一个新位置。
	 *
	 * $targetPath may be an absolute path, or a relative path. If it is a
	 * relative path, resolution should be the same as used by PHP's rename()
	 * function.
	 * $targetPath可以是绝对路径，也可以是相对路径。
	 *
	 * The original file MUST be removed on completion.
	 *
	 * If this method is called more than once, any subsequent calls MUST raise
	 * an exception.
	 *
	 * When used in an SAPI environment where $_FILES is populated, when writing
	 * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
	 * used to ensure permissions and upload status are verified correctly.
	 *
	 * If you wish to move to a stream, use getStream(), as SAPI operations
	 * cannot guarantee writing to stream destinations.
	 *
	 * @see http://php.net/is_uploaded_file
	 * @see http://php.net/move_uploaded_file
	 *
	 * @param string $targetPath Path to which to move the uploaded file.
	 * @param string $name       the name to rename the file to.
	 *
	 * @throws InvalidArgumentException if the $path specified is invalid.
	 * @throws RuntimeException on any error during the move operation.
	 * @throws RuntimeException on the second or subsequent call to the method.
	 */
	public function move(string $targetPath, string $name = null);

	//--------------------------------------------------------------------

	/**
	 * Returns whether the file has been moved or not. If it has,
	 * the move() method will not work and certain properties, like
	 * the tempName, will no longer be available.
	 *
	 * @return boolean
	 */
	public function hasMoved(): bool;

	//--------------------------------------------------------------------

	/**
	 * Retrieve the error associated with the uploaded file.
	 * 检索与上传文件相关的错误。
	 *
	 * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
	 *
	 * If the file was uploaded successfully, this method MUST return
	 * UPLOAD_ERR_OK.
	 *
	 * Implementations SHOULD return the value stored in the "error" key of
	 * the file in the $_FILES array.
	 *
	 * @see    http://php.net/manual/en/features.file-upload.errors.php
	 * @return integer One of PHP's UPLOAD_ERR_XXX constants.
	 */
	public function getError(): int;

	//--------------------------------------------------------------------

	/**
	 * Retrieve the filename sent by the client.
	 * 检索客户端发送的文件名。
	 *
	 * Do not trust the value returned by this method. A client could send
	 * a malicious filename with the intention to corrupt or hack your
	 * application.
	 * 不要信任此方法返回的值。客户端可能会发送恶意文件名，意图损坏或入侵您的应用程序。
	 *
	 * Implementations SHOULD return the value stored in the "name" key of
	 * the file in the $_FILES array.
	 *
	 * @return string The filename sent by the client or null if none
	 *     was provided.
	 */
	public function getName(): string;

	//--------------------------------------------------------------------

	/**
	 * Gets the temporary filename where the file was uploaded to.
	 * 获取文件上载到的临时文件名
	 *
	 * @return string
	 */
	public function getTempName(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the original file extension, based on the file name that
	 * was uploaded. This is NOT a trusted source.
	 * For a trusted version, use guessExtension() instead.
	 * 根据上传的文件名返回原始文件扩展名。此来源不可信。
	 *
	 * @return string
	 */
	public function getClientExtension(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns the mime type as provided by the client.
	 * This is NOT a trusted value.
	 * For a trusted version, use getMimeType() instead.
	 * 返回客户端提供的 MIME 类型。此值不具可信性。
	 *
	 * @return string
	 */
	public function getClientMimeType(): string;

	//--------------------------------------------------------------------

	/**
	 * Returns whether the file was uploaded successfully, based on whether
	 * it was uploaded via HTTP and has no errors.
	 * 根据文件是否通过 HTTP 上传且无错误，判断文件上传是否成功。
	 *
	 * @return boolean
	 */
	public function isValid(): bool;

	//--------------------------------------------------------------------

	/**
	 * Returns the destination path for the move operation where overwriting is not expected.
	 * 返回移动操作的目标路径，其中不期望覆盖。
	 *
	 * First, it checks whether the delimiter is present in the filename, if it is, then it checks whether the
	 * last element is an integer as there may be cases that the delimiter may be present in the filename.
	 * For the all other cases, it appends an integer starting from zero before the file's extension.
	 * 首先，它检查分隔符是否存在于文件名中；如果存在，则检查最后一个元素是否为整数，因为可能存在分隔符出现在文件名中的情况。
	 * 对于其余所有情况，它会在文件扩展名前添加一个从零开始的整数。
	 *
	 * @param string  $destination
	 * @param string  $delimiter
	 * @param integer $i
	 *
	 * @return string
	 */
	public function getDestination(string $destination, string $delimiter = '_', int $i = 0): string;

	//--------------------------------------------------------------------
}
