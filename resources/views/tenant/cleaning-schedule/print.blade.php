<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleaning Checklist - {{ $checklist->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .print-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #3B82F6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 10px;
        }
        
        .company-details {
            color: #6B7280;
            line-height: 1.6;
        }
        
        .checklist-info {
            text-align: right;
            flex: 0 0 300px;
        }
        
        .checklist-title {
            font-size: 28px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 10px;
        }
        
        .checklist-details {
            background-color: #F3F4F6;
            padding: 15px;
            border-radius: 5px;
            text-align: left;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .type-standard { background-color: #DBEAFE; color: #1E40AF; }
        .type-deep_clean { background-color: #FEF3C7; color: #92400E; }
        .type-maintenance { background-color: #F3E8FF; color: #7C3AED; }
        .type-inspection { background-color: #ECFDF5; color: #047857; }
        
        .info-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #F9FAFB;
            border-left: 4px solid #3B82F6;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 10px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        
        .detail-item {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 2px;
        }
        
        .detail-value {
            color: #6B7280;
        }
        
        .description-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #FFFBEB;
            border-left: 4px solid #F59E0B;
        }
        
        .checklist-items {
            margin: 30px 0;
        }
        
        .checklist-category {
            margin-bottom: 30px;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .category-header {
            background-color: #374151;
            color: white;
            padding: 15px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .category-items {
            padding: 0;
        }
        
        .checklist-item {
            display: flex;
            align-items: flex-start;
            padding: 12px 15px;
            border-bottom: 1px solid #F3F4F6;
            gap: 10px;
        }
        
        .checklist-item:last-child {
            border-bottom: none;
        }
        
        .checkbox {
            width: 16px;
            height: 16px;
            border: 2px solid #D1D5DB;
            border-radius: 3px;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        .item-content {
            flex-grow: 1;
        }
        
        .item-title {
            font-weight: 500;
            color: #374151;
            margin-bottom: 3px;
        }
        
        .item-description {
            font-size: 10px;
            color: #6B7280;
            line-height: 1.3;
        }
        
        .item-time {
            font-size: 10px;
            color: #9CA3AF;
            text-align: right;
            flex-shrink: 0;
            min-width: 60px;
        }
        
        .completion-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #EFF6FF;
            border-left: 4px solid #2563EB;
        }
        
        .signature-area {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #374151;
            height: 40px;
            margin-bottom: 8px;
        }
        
        .signature-label {
            font-size: 10px;
            color: #6B7280;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 10px;
        }
        
        .print-actions {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #F8FAFC;
            border-radius: 5px;
            border: 1px solid #E2E8F0;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background-color: #3B82F6;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6B7280;
            color: white;
        }
        
        .notes-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #FFFBEB;
            border: 1px dashed #D97706;
            border-radius: 5px;
        }
        
        .notes-title {
            font-weight: bold;
            color: #92400E;
            margin-bottom: 10px;
        }
        
        .notes-lines {
            height: 80px;
            background-image: repeating-linear-gradient(
                transparent,
                transparent 18px,
                #E5E7EB 18px,
                #E5E7EB 20px
            );
        }
        
        @media print {
            .print-actions {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 15px;
            }
            
            .print-header {
                page-break-inside: avoid;
            }
            
            .checklist-category {
                page-break-inside: avoid;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .signature-area {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Print Actions (hidden when printing) -->
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Checklist
        </button>
        <a href="{{ route('tenant.cleaning-schedule.show', $checklist) }}" class="btn btn-secondary">
            ⬅️ Back to Checklist
        </a>
    </div>

    <div class="print-header">
        <div class="company-info">
            <div class="company-name">{{ $property->name }}</div>
            <div class="company-details">
                @if($property->address)
                {{ $property->address }}<br>
                @endif
                @if($property->phone)
                Phone: {{ $property->phone }}<br>
                @endif
                @if($property->email)
                Email: {{ $property->email }}
                @endif
            </div>
        </div>
        <div class="checklist-info">
            <div class="checklist-title">CLEANING CHECKLIST</div>
            <div class="checklist-details">
                <strong>Checklist:</strong> {{ $checklist->name }}<br>
                <strong>Type:</strong>
                <span class="status-badge type-{{ $checklist->checklist_type }}">
                    {{ ucfirst(str_replace('_', ' ', $checklist->checklist_type)) }}
                </span><br>
                <strong>Created:</strong> {{ $checklist->created_at->format('F j, Y') }}<br>
                @if($checklist->estimated_minutes)
                <strong>Est. Duration:</strong> {{ $checklist->estimated_minutes }} minutes
                @endif
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="section-title">CHECKLIST INFORMATION</div>
        <div class="details-grid">
            <div>
                <div class="detail-item">
                    <div class="detail-label">Name:</div>
                    <div class="detail-value">{{ $checklist->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Type:</div>
                    <div class="detail-value">{{ ucfirst(str_replace('_', ' ', $checklist->checklist_type)) }}</div>
                </div>
                @if($checklist->roomType)
                <div class="detail-item">
                    <div class="detail-label">Room Type:</div>
                    <div class="detail-value">{{ $checklist->roomType->name }}</div>
                </div>
                @endif
            </div>
            <div>
                @if($checklist->estimated_minutes)
                <div class="detail-item">
                    <div class="detail-label">Estimated Duration:</div>
                    <div class="detail-value">{{ $checklist->estimated_minutes }} minutes</div>
                </div>
                @endif
                <div class="detail-item">
                    <div class="detail-label">Total Items:</div>
                    <div class="detail-value">{{ count($checklist->items ?? []) }} items</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Created:</div>
                    <div class="detail-value">{{ $checklist->created_at->format('M j, Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($checklist->description)
    <div class="description-section">
        <div class="section-title">DESCRIPTION</div>
        <div>{{ $checklist->description }}</div>
    </div>
    @endif

    <div class="checklist-items">
        <div class="section-title">CHECKLIST ITEMS</div>
        
        @if($checklist->items && count($checklist->items) > 0)
            @foreach($checklist->items as $index => $item)
            <div class="checklist-category">
                <div class="category-header">
                    Item {{ $index + 1 }} @if(isset($item['required']) && $item['required']) <span style="color: #DC2626;">(Required)</span> @endif
                </div>
                <div class="category-items">
                    <div class="checklist-item">
                        <div class="checkbox"></div>
                        <div class="item-content">
                            <div class="item-title">{{ $item['item'] ?? 'No description' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="checklist-category">
                <div class="category-header">No Items</div>
                <div class="category-items">
                    <div class="checklist-item">
                        <div class="item-content">
                            <div class="item-title" style="color: #6B7280; font-style: italic;">No checklist items have been added yet.</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="notes-section">
        <div class="notes-title">ADDITIONAL NOTES / OBSERVATIONS:</div>
        <div class="notes-lines"></div>
    </div>

    <div class="completion-section">
        <div class="section-title" style="color: #2563EB;">COMPLETION SIGN-OFF</div>
        <div class="signature-area">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Cleaner Signature & Date</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Supervisor Signature & Date</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-label">Start Time / End Time</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>Cleaning Checklist - {{ $property->name }}</div>
        <div>Printed on {{ now()->format('F j, Y \a\t g:i A') }}</div>
        <div>Property Management System</div>
    </div>

    <script>
        // Auto-focus on print when page loads
        window.addEventListener('load', function() {
            // Small delay to ensure page is fully rendered
            setTimeout(function() {
                if (window.location.search.includes('autoprint=1')) {
                    window.print();
                }
            }, 500);
        });
    </script>
</body>
</html>