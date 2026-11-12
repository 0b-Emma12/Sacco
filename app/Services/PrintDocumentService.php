<?php

namespace App\Services;

use App\Models\Member;
use App\Models\PrintLog;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use RuntimeException;

class PrintDocumentService
{
    public const TYPES = [
        'MEMBER_WELCOME' => ['prefix' => 'WLC', 'label' => 'Membership Welcome Pack'],
        'SHARE_CERTIFICATE' => ['prefix' => 'SCT', 'label' => 'Share Certificate'],
        'DEPOSIT_RECEIPT' => ['prefix' => 'RCT', 'label' => 'Deposit Receipt'],
        'LOAN_DISBURSEMENT' => ['prefix' => 'LDV', 'label' => 'Loan Disbursement Voucher'],
        'LOAN_REPAYMENT' => ['prefix' => 'RPY', 'label' => 'Loan Repayment Receipt'],
        'MEMBER_STATEMENT' => ['prefix' => 'STM', 'label' => 'Member Account Statement'],
        'DIVIDEND_STATEMENT' => ['prefix' => 'DIV', 'label' => 'Dividend & Interest Statement'],
        'WHT_CERTIFICATE' => ['prefix' => 'WHT', 'label' => 'Withholding Tax Certificate'],
        'LOAN_CLEARANCE' => ['prefix' => 'LCC', 'label' => 'Loan Clearance Certificate'],
        'GUARANTOR_RELEASE' => ['prefix' => 'GRL', 'label' => 'Guarantor Release Letter'],
        'EXPENSE_VOUCHER' => ['prefix' => 'PVT', 'label' => 'Payment Voucher'],
        'DEATH_SETTLEMENT' => ['prefix' => 'DCS', 'label' => 'Death Benefit Settlement Letter'],
        'SHARE_TRANSFER' => ['prefix' => 'STC', 'label' => 'Share Transfer Certificate'],
        'STANDING_ORDER' => ['prefix' => 'S05', 'label' => 'Standing Order Mandate'],
        'TRANSACTION_LEDGER' => ['prefix' => 'TLG', 'label' => 'Transaction Ledger Extract'],
    ];

    public static function label(string $type): string
    {
        return self::TYPES[$type]['label'] ?? Str::title(str_replace('_', ' ', $type));
    }

    /**
     * Issue and log the document as soon as it is opened; the page itself is the printable artifact.
     */
    public static function forRequest(string $type, ?Model $subject = null, ?Member $member = null, array $meta = []): PrintLog
    {
        return self::issue($type, $subject, $member, $meta);
    }

    public static function issue(string $type, ?Model $subject = null, ?Member $member = null, array $meta = []): PrintLog
    {
        $prefix = self::TYPES[$type]['prefix'] ?? 'DOC';
        $subjectType = $subject ? get_class($subject) : null;
        $subjectId = $subject ? (string) $subject->getKey() : null;

        $previous = PrintLog::query()
            ->where('document_type', $type)
            ->when($subjectType, function ($query) use ($subjectType, $subjectId) {
                $query->where('subject_type', $subjectType)->where('subject_id', $subjectId);
            })
            ->orderBy('id')
            ->first();

        $todayCount = PrintLog::where('document_type', $type)->whereDate('created_at', today())->count();

        $attributes = [
            'document_type' => $type,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'member_id' => $member ? $member->getKey() : null,
            'printed_by_id' => auth()->id(),
            'printed_by_name' => auth()->check()
                ? (auth()->user()->username ?? auth()->user()->email)
                : 'System',
            'watermark' => $previous ? 'COPY' : 'ORIGINAL',
            'is_reprint' => $previous !== null,
            'verification_token' => Str::random(48),
            'meta' => $meta + ($previous ? ['original_document_id' => $previous->document_id] : []),
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $attributes['document_id'] = sprintf('%s-%s-%05d', $prefix, now()->format('Ymd'), $todayCount + 1 + $attempt);

            try {
                return PrintLog::create($attributes);
            } catch (QueryException $e) {
                // Duplicate document number under concurrent printing; try the next one.
            }
        }

        throw new RuntimeException('Unable to allocate a document number. Please retry.');
    }

    public static function verificationUrl(PrintLog $log): string
    {
        return route('print.verify', [
            'document' => $log->document_id,
            'token' => $log->verification_token,
        ]);
    }

    public static function qrSvg(?PrintLog $log, int $size = 96): string
    {
        if (! $log || ! $log->exists) {
            return '';
        }

        $renderer = new ImageRenderer(new RendererStyle($size, 1), new SvgImageBackEnd());
        $svg = (new Writer($renderer))->writeString(self::verificationUrl($log));

        return preg_replace('/<\?xml[^>]*\?>\s*/', '', $svg);
    }
}
