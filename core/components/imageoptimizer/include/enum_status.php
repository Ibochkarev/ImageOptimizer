<?php

defined('MODX_CORE_PATH') || exit;

enum QueueStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Done = 'done';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function tagSeverity(): string
    {
        return match ($this) {
            self::Pending => 'warn',
            self::Processing => 'info',
            self::Done => 'success',
            self::Failed => 'danger',
            self::Skipped => 'secondary',
        };
    }
}

enum SkipReason: string
{
    case SvgSkip = 'svg_skip';
    case AnimatedNotSupported = 'animated_not_supported';
    case HeicNoDecoder = 'heic_no_decoder';
    case AlreadyWebp = 'already_webp';
    case UpscaleSkip = 'upscale_skip';
    case MinWidthSkip = 'min_width_skip';
    case MemoryLimit = 'memory_limit';
    case NonFilesystemSource = 'non_filesystem_source';
    case ParentIsPicture = 'parent_is_picture';
    case HasSrcset = 'has_srcset';
    case SrcPattern = 'src_pattern';
    case SkipClass = 'skip_class';
    case DataSkip = 'data_skip';
    case MetaParent = 'meta_parent';
}
