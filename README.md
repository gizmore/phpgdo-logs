# phpgdo-logs

`Logs` provides log retention, rotation and a web-only log viewer for PHPGDO.

## Access

- Users can view only their own log entries.
- Staff can inspect all log entries and use the overview for support and
  incident review.
- The module intentionally exposes no chat connector methods.

## Retention and rotation

- A daily cronjob archives log files older than `logs.log_keep_for_time`
  (default: `7d`).
- Archives are ZIP files stored below `protected/zipped/`.
- When `logs.log_by_mail` is enabled, the rotated log archive is also sent to
  the address configured in `logs.log_mail_to`.
- `logs.log_delete_after_mail` optionally removes the local archive, but only
  after the mailer reports a successful delivery hand-off.

## Dependencies

- Required dependency: `ZIP`
- Optional friendency: `Mail`

Mail is therefore available when installed, but the local ZIP rotation remains
functional without it.
