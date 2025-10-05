<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Request #{{ $maintenanceRequest->id }}</title>
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
        
        .request-info {
            text-align: right;
            flex: 0 0 300px;
        }
        
        .request-title {
            font-size: 28px;
            font-weight: bold;
            color: #1F2937;
            margin-bottom: 10px;
        }
        
        .request-details {
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
        
        .status-completed { background-color: #D1FAE5; color: #065F46; }
        .status-in_progress { background-color: #FEF3C7; color: #92400E; }
        .status-pending { background-color: #DBEAFE; color: #1E40AF; }
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; }
        
        .priority-urgent { background-color: #FEE2E2; color: #991B1B; }
        .priority-high { background-color: #FEF3C7; color: #92400E; }
        .priority-normal { background-color: #DBEAFE; color: #1E40AF; }
        .priority-low { background-color: #D1FAE5; color: #065F46; }
        
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
        
        .tasks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .tasks-table th {
            background-color: #374151;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        
        .tasks-table td {
            padding: 12px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .work-log-section {
            background-color: #EFF6FF;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .work-log-entry {
            border-bottom: 1px solid #E5E7EB;
            padding: 10px 0;
            margin-bottom: 10px;
        }
        
        .work-log-entry:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .work-log-header {
            font-weight: bold;
            color: #1E40AF;
            margin-bottom: 5px;
        }
        
        .work-log-meta {
            font-size: 10px;
            color: #6B7280;
            margin-bottom: 5px;
        }
        
        .cost-summary {
            background-color: #ECFDF5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .cost-summary-title {
            font-weight: bold;
            color: #047857;
            margin-bottom: 10px;
        }
        
        .cost-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            text-align: center;
        }
        
        .cost-item {
            padding: 10px;
            background-color: white;
            border-radius: 3px;
        }
        
        .cost-value {
            font-size: 16px;
            font-weight: bold;
            color: #047857;
        }
        
        .cost-label {
            font-size: 10px;
            color: #6B7280;
            margin-top: 3px;
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
        
        .special-notes {
            background-color: #FEF3C7;
            padding: 10px;
            border-radius: 3px;
            margin-top: 10px;
            font-size: 11px;
            color: #92400E;
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
            
            .tasks-table {
                page-break-inside: avoid;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .cost-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Print Actions (hidden when printing) -->
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Print Report
        </button>
        <a href="{{ route('tenant.maintenance.show', $maintenanceRequest) }}" class="btn btn-secondary">
            ⬅️ Back to Request
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
        <div class="request-info">
            <div class="request-title">MAINTENANCE REPORT</div>
            <div class="request-details">
                <strong>Request #:</strong> {{ $maintenanceRequest->id }}<br>
                <strong>Created:</strong> {{ $maintenanceRequest->created_at->format('F j, Y g:i A') }}<br>
                <strong>Priority:</strong>
                <span class="status-badge priority-{{ $maintenanceRequest->priority }}">
                    {{ ucfirst($maintenanceRequest->priority) }}
                </span><br>
                <strong>Status:</strong>
                <span class="status-badge status-{{ $maintenanceRequest->status }}">
                    {{ ucfirst(str_replace('_', ' ', $maintenanceRequest->status)) }}
                </span>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="section-title">REQUEST INFORMATION</div>
        <div class="details-grid">
            <div>
                <div class="detail-item">
                    <div class="detail-label">Title:</div>
                    <div class="detail-value">{{ $maintenanceRequest->title }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Property:</div>
                    <div class="detail-value">{{ $maintenanceRequest->property->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Room:</div>
                    <div class="detail-value">
                        @if($maintenanceRequest->room)
                            Room {{ $maintenanceRequest->room->number }} - {{ $maintenanceRequest->room->type->name }}
                        @else
                            Property-wide
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Category:</div>
                    <div class="detail-value">{{ ucfirst(str_replace('_', ' ', $maintenanceRequest->category)) }}</div>
                </div>
                @if($maintenanceRequest->location_details)
                <div class="detail-item">
                    <div class="detail-label">Specific Location:</div>
                    <div class="detail-value">{{ $maintenanceRequest->location_details }}</div>
                </div>
                @endif
            </div>
            <div>
                <div class="detail-item">
                    <div class="detail-label">Reported By:</div>
                    <div class="detail-value">{{ $maintenanceRequest->reportedBy->name ?? 'System' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Assigned To:</div>
                    <div class="detail-value">{{ $maintenanceRequest->assignedTo->name ?? 'Unassigned' }}</div>
                </div>
                @if($maintenanceRequest->scheduled_for)
                <div class="detail-item">
                    <div class="detail-label">Scheduled For:</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($maintenanceRequest->scheduled_for)->format('M j, Y g:i A') }}</div>
                </div>
                @endif
                @if($maintenanceRequest->estimated_cost)
                <div class="detail-item">
                    <div class="detail-label">Estimated Cost:</div>
                    <div class="detail-value">${{ number_format($maintenanceRequest->estimated_cost, 2) }}</div>
                </div>
                @endif
                @if($maintenanceRequest->completed_at)
                <div class="detail-item">
                    <div class="detail-label">Completed:</div>
                    <div class="detail-value">{{ $maintenanceRequest->completed_at->format('M j, Y g:i A') }}</div>
                </div>
                @endif
            </div>
        </div>
        
        @if($maintenanceRequest->requires_room_closure || $maintenanceRequest->affects_guest_experience)
        <div class="special-notes">
            <strong>Special Notes:</strong>
            @if($maintenanceRequest->requires_room_closure)
            • Requires room closure
            @endif
            @if($maintenanceRequest->affects_guest_experience)
            • Affects guest experience
            @endif
        </div>
        @endif
    </div>

    <div class="description-section">
        <div class="section-title">DESCRIPTION</div>
        <div>{{ $maintenanceRequest->description }}</div>
    </div>

    @if($maintenanceRequest->maintenanceTasks->count() > 0)
    <div class="info-section">
        <div class="section-title">MAINTENANCE TASKS</div>
        <table class="tasks-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th class="text-center">Type</th>
                    <th class="text-center">Priority</th>
                    <th class="text-center">Status</th>
                    <th>Assigned To</th>
                    <th class="text-right">Est. Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($maintenanceRequest->maintenanceTasks as $task)
                <tr>
                    <td>
                        <strong>{{ $task->title }}</strong>
                        @if($task->description)
                        <br><small style="color: #6B7280;">{{ $task->description }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ ucfirst($task->task_type) }}</td>
                    <td class="text-center">
                        <span class="status-badge priority-{{ $task->priority }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $task->status }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                    <td>{{ $task->assignedTo->name ?? 'Unassigned' }}</td>
                    <td class="text-right">{{ $task->estimated_minutes ? $task->estimated_minutes . ' min' : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @php
    $workLogs = collect();
    foreach($maintenanceRequest->maintenanceTasks ?? [] as $task) {
        foreach($task->staffHours ?? [] as $hour) {
            $workLogs->push($hour);
        }
    }
    $workLogs = $workLogs->sortByDesc('created_at');
    @endphp

    @if($workLogs->count() > 0)
    <div class="work-log-section">
        <div class="section-title" style="color: #1E40AF;">WORK LOG</div>
        @foreach($workLogs as $log)
        <div class="work-log-entry">
            <div class="work-log-header">{{ $log->user->name }}</div>
            <div class="work-log-meta">
                {{ $log->work_date }} 
                @if($log->start_time)
                {{ $log->start_time }} - {{ $log->end_time }}
                @endif
                • {{ $log->hours_worked }} hours
                @if($log->is_overtime)
                • Overtime
                @endif
            </div>
            <div>{{ $log->description }}</div>
            @if($log->materials_used)
            <div style="font-size: 10px; color: #6B7280; margin-top: 3px;">
                <strong>Materials:</strong> {{ $log->materials_used }}
            </div>
            @endif
            @if($log->notes)
            <div style="font-size: 10px; color: #6B7280; margin-top: 3px;">
                <strong>Notes:</strong> {{ $log->notes }}
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="cost-summary">
        <div class="cost-summary-title">COST SUMMARY</div>
        @php
            $totalLaborCost = $workLogs->sum('total_amount');
            $totalMaterialsCost = $maintenanceRequest->maintenanceTasks->sum('actual_cost');
            $totalCost = $totalLaborCost + $totalMaterialsCost;
        @endphp
        <div class="cost-grid">
            <div class="cost-item">
                <div class="cost-value">{{ $workLogs->sum('hours_worked') }}</div>
                <div class="cost-label">Total Hours</div>
            </div>
            <div class="cost-item">
                <div class="cost-value">${{ number_format($totalLaborCost, 2) }}</div>
                <div class="cost-label">Labor Cost</div>
            </div>
            <div class="cost-item">
                <div class="cost-value">${{ number_format($totalMaterialsCost, 2) }}</div>
                <div class="cost-label">Materials</div>
            </div>
            <div class="cost-item">
                <div class="cost-value">${{ number_format($totalCost, 2) }}</div>
                <div class="cost-label">Total Cost</div>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        <div>Maintenance Report - {{ $property->name }}</div>
        <div>Report printed on {{ now()->format('F j, Y \a\t g:i A') }}</div>
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