<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { padding: 24px 32px; color: #fff; }
        .header.created { background: #16a34a; }
        .header.updated { background: #2563eb; }
        .header.deleted { background: #dc2626; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p  { margin: 6px 0 0; opacity: .85; font-size: 14px; }
        .body { padding: 28px 32px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; margin-bottom: 20px; }
        .badge.created { background: #dcfce7; color: #15803d; }
        .badge.updated { background: #dbeafe; color: #1d4ed8; }
        .badge.deleted { background: #fee2e2; color: #b91c1c; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { text-align: left; background: #f9fafb; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; padding: 10px 14px; border: 1px solid #e5e7eb; }
        td { padding: 10px 14px; border: 1px solid #e5e7eb; font-size: 14px; color: #374151; word-break: break-word; }
        .footer { padding: 16px 32px; background: #f9fafb; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="container">

    <div class="header {{ $action }}">
        <h1>
            @if($action === 'created') ✅
            @elseif($action === 'updated') ✏️
            @else 🗑️
            @endif
            {{ $entityType }}
        </h1>
        <p>{{ $performedAt }}</p>
    </div>

    <div class="body">
        <span class="badge {{ $action }}">
            @if($action === 'created') СОЗДАН
            @elseif($action === 'updated') ОБНОВЛЁН
            @else УДАЛЁН
            @endif
        </span>

        <table>
            <tr>
                <th>Поле</th>
                <th>Значение</th>
            </tr>
            @foreach($entityData as $field => $value)
                <tr>
                    <td><strong>{{ $field }}</strong></td>
                    <td>
                        @if(is_array($value))
                            {{ implode(', ', $value) }}
                        @elseif(is_bool($value))
                            {{ $value ? 'Да' : 'Нет' }}
                        @elseif(is_null($value))
                            <span style="color:#9ca3af">—</span>
                        @else
                            {{ $value }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="footer">
        SmartBook · Автоматическое уведомление · Не отвечайте на это письмо
    </div>
</div>
</body>
</html>
