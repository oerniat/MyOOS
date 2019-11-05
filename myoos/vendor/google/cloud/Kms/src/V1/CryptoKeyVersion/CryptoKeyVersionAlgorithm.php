<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/cloud/kms/v1/resources.proto

namespace Google\Cloud\Kms\V1\CryptoKeyVersion;

use UnexpectedValueException;

/**
 * The algorithm of the [CryptoKeyVersion][google.cloud.kms.v1.CryptoKeyVersion], indicating what
 * parameters must be used for each cryptographic operation.
 * The
 * [GOOGLE_SYMMETRIC_ENCRYPTION][google.cloud.kms.v1.CryptoKeyVersion.CryptoKeyVersionAlgorithm.GOOGLE_SYMMETRIC_ENCRYPTION]
 * algorithm is usable with [CryptoKey.purpose][google.cloud.kms.v1.CryptoKey.purpose]
 * [ENCRYPT_DECRYPT][google.cloud.kms.v1.CryptoKey.CryptoKeyPurpose.ENCRYPT_DECRYPT].
 * Algorithms beginning with "RSA_SIGN_" are usable with [CryptoKey.purpose][google.cloud.kms.v1.CryptoKey.purpose]
 * [ASYMMETRIC_SIGN][google.cloud.kms.v1.CryptoKey.CryptoKeyPurpose.ASYMMETRIC_SIGN].
 * The fields in the name after "RSA_SIGN_" correspond to the following
 * parameters: padding algorithm, modulus bit length, and digest algorithm.
 * For PSS, the salt length used is equal to the length of digest
 * algorithm. For example,
 * [RSA_SIGN_PSS_2048_SHA256][google.cloud.kms.v1.CryptoKeyVersion.CryptoKeyVersionAlgorithm.RSA_SIGN_PSS_2048_SHA256]
 * will use PSS with a salt length of 256 bits or 32 bytes.
 * Algorithms beginning with "RSA_DECRYPT_" are usable with
 * [CryptoKey.purpose][google.cloud.kms.v1.CryptoKey.purpose]
 * [ASYMMETRIC_DECRYPT][google.cloud.kms.v1.CryptoKey.CryptoKeyPurpose.ASYMMETRIC_DECRYPT].
 * The fields in the name after "RSA_DECRYPT_" correspond to the following
 * parameters: padding algorithm, modulus bit length, and digest algorithm.
 * Algorithms beginning with "EC_SIGN_" are usable with [CryptoKey.purpose][google.cloud.kms.v1.CryptoKey.purpose]
 * [ASYMMETRIC_SIGN][google.cloud.kms.v1.CryptoKey.CryptoKeyPurpose.ASYMMETRIC_SIGN].
 * The fields in the name after "EC_SIGN_" correspond to the following
 * parameters: elliptic curve, digest algorithm.
 * For more information, see [Key purposes and algorithms]
 * (https://cloud.google.com/kms/docs/algorithms).
 *
 * Protobuf type <code>google.cloud.kms.v1.CryptoKeyVersion.CryptoKeyVersionAlgorithm</code>
 */
class CryptoKeyVersionAlgorithm
{
    /**
     * Not specified.
     *
     * Generated from protobuf enum <code>CRYPTO_KEY_VERSION_ALGORITHM_UNSPECIFIED = 0;</code>
     */
    const CRYPTO_KEY_VERSION_ALGORITHM_UNSPECIFIED = 0;
    /**
     * Creates symmetric encryption keys.
     *
     * Generated from protobuf enum <code>GOOGLE_SYMMETRIC_ENCRYPTION = 1;</code>
     */
    const GOOGLE_SYMMETRIC_ENCRYPTION = 1;
    /**
     * RSASSA-PSS 2048 bit key with a SHA256 digest.
     *
     * Generated from protobuf enum <code>RSA_SIGN_PSS_2048_SHA256 = 2;</code>
     */
    const RSA_SIGN_PSS_2048_SHA256 = 2;
    /**
     * RSASSA-PSS 3072 bit key with a SHA256 digest.
     *
     * Generated from protobuf enum <code>RSA_SIGN_PSS_3072_SHA256 = 3;</code>
     */
    const RSA_SIGN_PSS_3072_SHA256 = 3;
    /**
     * RSASSA-PSS 4096 bit key with a SHA256 digest.
     *
     * Generated from protobuf enum <code>RSA_SIGN_PSS_4096_SHA256 = 4;</code>
     */
    const RSA_SIGN_PSS_4096_SHA256 = 4;
    /**
     * RSASSA-PSS 4096 bit key with a SHA512 digest.
     *
     * Generated from protobuf enum <code>RSA_SIGN_PSS_4096_SHA512 = 15;</code>
     */
    const RSA_SIGN_PSS_4096_SHA512 = 15;
    /**
     * RSASSA-PKCS1-v1_5 with a 2048 bit key and a SHA256 digest.
     *
     * Generated from protobuf enum <code>RSA_SIGN_PKCS1_2048_SHA256 = 5;</code>
     */
    const RSA_SIGN_PKCS1_2048_SHA256 = 5;
    /**
     * RSASSA-PKCS1-v1_5 with a 3072 bit key and a SHA256 digest.
     *
     * Generated from protobuf enum <code>RSA_SIGN_PKCS1_3072_SHA256 = 6;</code>
     */
    const RSA_SIGN_PKCS1_3072_SHA256 = 6;
    /**
     * RSASSA-PKCS1-v1_5 with a 4096 bit key and a SHA256 digest.
     *
     * Generated from protobuf enum <code>RSA_SIGN_PKCS1_4096_SHA256 = 7;</code>
     */
    const RSA_SIGN_PKCS1_4096_SHA256 = 7;
    /**
     * RSASSA-PKCS1-v1_5 with a 4096 bit key and a SHA512 digest.
     *
     * Generated from protobuf enum <code>RSA_SIGN_PKCS1_4096_SHA512 = 16;</code>
     */
    const RSA_SIGN_PKCS1_4096_SHA512 = 16;
    /**
     * RSAES-OAEP 2048 bit key with a SHA256 digest.
     *
     * Generated from protobuf enum <code>RSA_DECRYPT_OAEP_2048_SHA256 = 8;</code>
     */
    const RSA_DECRYPT_OAEP_2048_SHA256 = 8;
    /**
     * RSAES-OAEP 3072 bit key with a SHA256 digest.
     *
     * Generated from protobuf enum <code>RSA_DECRYPT_OAEP_3072_SHA256 = 9;</code>
     */
    const RSA_DECRYPT_OAEP_3072_SHA256 = 9;
    /**
     * RSAES-OAEP 4096 bit key with a SHA256 digest.
     *
     * Generated from protobuf enum <code>RSA_DECRYPT_OAEP_4096_SHA256 = 10;</code>
     */
    const RSA_DECRYPT_OAEP_4096_SHA256 = 10;
    /**
     * RSAES-OAEP 4096 bit key with a SHA512 digest.
     *
     * Generated from protobuf enum <code>RSA_DECRYPT_OAEP_4096_SHA512 = 17;</code>
     */
    const RSA_DECRYPT_OAEP_4096_SHA512 = 17;
    /**
     * ECDSA on the NIST P-256 curve with a SHA256 digest.
     *
     * Generated from protobuf enum <code>EC_SIGN_P256_SHA256 = 12;</code>
     */
    const EC_SIGN_P256_SHA256 = 12;
    /**
     * ECDSA on the NIST P-384 curve with a SHA384 digest.
     *
     * Generated from protobuf enum <code>EC_SIGN_P384_SHA384 = 13;</code>
     */
    const EC_SIGN_P384_SHA384 = 13;

    private static $valueToName = [
        self::CRYPTO_KEY_VERSION_ALGORITHM_UNSPECIFIED => 'CRYPTO_KEY_VERSION_ALGORITHM_UNSPECIFIED',
        self::GOOGLE_SYMMETRIC_ENCRYPTION => 'GOOGLE_SYMMETRIC_ENCRYPTION',
        self::RSA_SIGN_PSS_2048_SHA256 => 'RSA_SIGN_PSS_2048_SHA256',
        self::RSA_SIGN_PSS_3072_SHA256 => 'RSA_SIGN_PSS_3072_SHA256',
        self::RSA_SIGN_PSS_4096_SHA256 => 'RSA_SIGN_PSS_4096_SHA256',
        self::RSA_SIGN_PSS_4096_SHA512 => 'RSA_SIGN_PSS_4096_SHA512',
        self::RSA_SIGN_PKCS1_2048_SHA256 => 'RSA_SIGN_PKCS1_2048_SHA256',
        self::RSA_SIGN_PKCS1_3072_SHA256 => 'RSA_SIGN_PKCS1_3072_SHA256',
        self::RSA_SIGN_PKCS1_4096_SHA256 => 'RSA_SIGN_PKCS1_4096_SHA256',
        self::RSA_SIGN_PKCS1_4096_SHA512 => 'RSA_SIGN_PKCS1_4096_SHA512',
        self::RSA_DECRYPT_OAEP_2048_SHA256 => 'RSA_DECRYPT_OAEP_2048_SHA256',
        self::RSA_DECRYPT_OAEP_3072_SHA256 => 'RSA_DECRYPT_OAEP_3072_SHA256',
        self::RSA_DECRYPT_OAEP_4096_SHA256 => 'RSA_DECRYPT_OAEP_4096_SHA256',
        self::RSA_DECRYPT_OAEP_4096_SHA512 => 'RSA_DECRYPT_OAEP_4096_SHA512',
        self::EC_SIGN_P256_SHA256 => 'EC_SIGN_P256_SHA256',
        self::EC_SIGN_P384_SHA384 => 'EC_SIGN_P384_SHA384',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(CryptoKeyVersionAlgorithm::class, \Google\Cloud\Kms\V1\CryptoKeyVersion_CryptoKeyVersionAlgorithm::class);

