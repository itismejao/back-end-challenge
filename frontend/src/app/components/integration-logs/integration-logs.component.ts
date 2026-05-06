import { Component, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { IntegrationLogService } from '../../services/integration-log.service';
import { IntegrationLog, LogStatus } from '../../models/integration-log.model';

@Component({
  selector: 'app-integration-logs',
  imports: [DatePipe, RouterLink],
  template: `
    <div class="container">
      <header class="header">
        <div class="header-left">
          <h1 class="logo">OneRPM</h1>
          <span class="subtitle">Integration Logs</span>
        </div>
        <a routerLink="/" class="back-link">&larr; Tracks</a>
      </header>

      <div class="filters">
        <div class="filter-group">
          <label>Status</label>
          <select [value]="statusFilter()" (change)="onStatusChange($event)">
            <option value="">Todos</option>
            <option value="pending">Pending</option>
            <option value="success">Success</option>
            <option value="not_found">Not Found</option>
            <option value="failed">Failed</option>
          </select>
        </div>

        <div class="filter-group">
          <label>ISRC</label>
          <input
            type="text"
            [value]="isrcFilter()"
            (input)="onIsrcInput($event)"
            placeholder="Ex: NO1R42509310"
            maxlength="12"
          />
        </div>

        <button class="filter-btn" (click)="applyFilters()">Filtrar</button>
      </div>

      <div class="logs-table">
        <div class="table-header">
          <span class="col-status">Status</span>
          <span class="col-isrc">ISRC</span>
          <span class="col-provider">Provider</span>
          <span class="col-track">Track</span>
          <span class="col-duration">Tempo</span>
          <span class="col-attempt">Tentativa</span>
          <span class="col-date">Data</span>
        </div>

        @if (loading() && logs().length === 0) {
          @for (_ of skeletons; track $index) {
            <div class="table-row skeleton-row">
              <span class="col-status"><div class="skeleton-pill skeleton-pulse"></div></span>
              <span class="col-isrc"><div class="skeleton-text skeleton-pulse"></div></span>
              <span class="col-provider"><div class="skeleton-text-sm skeleton-pulse"></div></span>
              <span class="col-track"><div class="skeleton-text skeleton-pulse"></div></span>
              <span class="col-duration"><div class="skeleton-text-sm skeleton-pulse"></div></span>
              <span class="col-attempt"><div class="skeleton-text-xs skeleton-pulse"></div></span>
              <span class="col-date"><div class="skeleton-text skeleton-pulse"></div></span>
            </div>
          }
        }

        @if (!loading() && logs().length === 0) {
          <div class="empty">Nenhum log encontrado</div>
        }

        @for (log of logs(); track log.id) {
          <div class="table-row" [class.expanded]="expandedId() === log.id" (click)="toggleExpand(log.id)">
            <span class="col-status">
              <span class="status-badge" [attr.data-status]="log.status">{{ statusLabel(log.status) }}</span>
            </span>
            <span class="col-isrc mono">{{ log.isrc }}</span>
            <span class="col-provider">{{ log.provider_code }}</span>
            <span class="col-track">{{ log.track?.name || '-' }}</span>
            <span class="col-duration">{{ formatDuration(log.duration_ms) }}</span>
            <span class="col-attempt">#{{ log.attempt }}</span>
            <span class="col-date">{{ log.started_at | date:'dd/MM HH:mm:ss' }}</span>
          </div>

          @if (expandedId() === log.id && log.error_message) {
            <div class="error-detail">
              <span class="error-class">{{ log.error_class }}</span>
              <pre class="error-message">{{ log.error_message }}</pre>
              @if (log.markets && log.markets.length > 0) {
                <span class="markets">Markets: {{ log.markets.join(', ') }}</span>
              }
            </div>
          }
        }

        @if (loading() && logs().length > 0) {
          @for (_ of skeletons; track $index) {
            <div class="table-row skeleton-row">
              <span class="col-status"><div class="skeleton-pill skeleton-pulse"></div></span>
              <span class="col-isrc"><div class="skeleton-text skeleton-pulse"></div></span>
              <span class="col-provider"><div class="skeleton-text-sm skeleton-pulse"></div></span>
              <span class="col-track"><div class="skeleton-text skeleton-pulse"></div></span>
              <span class="col-duration"><div class="skeleton-text-sm skeleton-pulse"></div></span>
              <span class="col-attempt"><div class="skeleton-text-xs skeleton-pulse"></div></span>
              <span class="col-date"><div class="skeleton-text skeleton-pulse"></div></span>
            </div>
          }
        }

        @if (nextCursor() && !loading()) {
          <button class="load-more" (click)="loadMore()">Carregar mais</button>
        }
      </div>
    </div>
  `,
  styles: [`
    .container { max-width: 1100px; margin: 0 auto; padding: 24px 16px; }

    .header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 24px;
    }
    .header-left { display: flex; align-items: baseline; gap: 12px; }
    .logo { margin: 0; font-size: 28px; font-weight: 800; color: #1db954; letter-spacing: -0.5px; }
    .subtitle { color: #666; font-size: 14px; }
    .back-link {
      color: #999; text-decoration: none; font-size: 14px;
      transition: color 0.2s;
    }
    .back-link:hover { color: #1db954; }

    .filters {
      display: flex; align-items: center; gap: 16px; margin-bottom: 24px;
      flex-wrap: wrap;
    }
    .filter-group { display: flex; align-items: center; gap: 8px; }
    .filters label { color: #999; font-size: 13px; }
    .filters select, .filters input {
      padding: 8px 12px; border: 1px solid #333; border-radius: 8px;
      background: #1e1e1e; color: #fff; font-size: 14px; outline: none;
    }
    .filters input { width: 150px; }
    .filters select:focus, .filters input:focus { border-color: #1db954; }
    .filter-btn {
      padding: 8px 20px; border: 1px solid #1db954; border-radius: 8px;
      background: none; color: #1db954; font-size: 14px; font-weight: 600;
      cursor: pointer; transition: background 0.2s;
    }
    .filter-btn:hover { background: rgba(29, 185, 84, 0.1); }

    .logs-table { display: flex; flex-direction: column; gap: 2px; }

    .table-header {
      display: flex; padding: 12px 16px; color: #666; font-size: 12px;
      text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;
    }

    .table-row {
      display: flex; padding: 14px 16px; background: #181818; border-radius: 8px;
      align-items: center; cursor: pointer; transition: background 0.2s;
    }
    .table-row:hover { background: #222; }
    .table-row.expanded { background: #222; border-bottom-left-radius: 0; border-bottom-right-radius: 0; }

    .col-status { width: 100px; flex-shrink: 0; }
    .col-isrc { width: 140px; flex-shrink: 0; }
    .col-provider { width: 90px; flex-shrink: 0; }
    .col-track { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .col-duration { width: 100px; flex-shrink: 0; text-align: right; }
    .col-attempt { width: 100px; flex-shrink: 0; text-align: center; }
    .col-date { width: 120px; flex-shrink: 0; text-align: right; color: #666; font-size: 13px; }

    .mono { font-family: monospace; font-size: 13px; color: #999; }

    .status-badge {
      padding: 3px 8px; border-radius: 4px; font-size: 11px;
      font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;
    }
    [data-status="success"] { background: rgba(29, 185, 84, 0.15); color: #1db954; }
    [data-status="failed"] { background: rgba(255, 85, 85, 0.15); color: #ff5555; }
    [data-status="not_found"] { background: rgba(255, 170, 50, 0.15); color: #ffaa32; }
    [data-status="pending"] { background: rgba(100, 100, 100, 0.2); color: #999; }

    .error-detail {
      padding: 12px 16px 16px; background: #1a1a1a; border-radius: 0 0 8px 8px;
      border-top: 1px solid #2a2a2a;
    }
    .error-class { color: #ff5555; font-size: 12px; font-weight: 600; }
    .error-message {
      margin: 8px 0; padding: 10px; background: #111; border-radius: 6px;
      color: #ccc; font-size: 12px; white-space: pre-wrap; word-break: break-all;
      max-height: 150px; overflow-y: auto;
    }
    .markets { color: #666; font-size: 12px; }

    .skeleton-row { pointer-events: none; }
    .skeleton-pulse {
      background: linear-gradient(90deg, #282828 25%, #333 50%, #282828 75%);
      background-size: 200% 100%; animation: pulse 1.5s ease-in-out infinite; border-radius: 4px;
    }
    @keyframes pulse { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    .skeleton-pill { height: 20px; width: 70px; border-radius: 4px; }
    .skeleton-text { height: 14px; width: 100%; }
    .skeleton-text-sm { height: 14px; width: 60px; }
    .skeleton-text-xs { height: 14px; width: 30px; }

    .empty { text-align: center; padding: 48px 0; color: #666; }

    .load-more {
      margin: 24px auto 0; padding: 12px 32px; border: 1px solid #333;
      border-radius: 24px; background: none; color: #fff;
      font-size: 14px; font-weight: 600; cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
    }
    .load-more:hover { border-color: #1db954; background: rgba(29, 185, 84, 0.1); }
  `],
})
export class IntegrationLogsComponent {
  private logService = inject(IntegrationLogService);

  logs = signal<IntegrationLog[]>([]);
  loading = signal(false);
  nextCursor = signal<string | null>(null);
  expandedId = signal<number | null>(null);
  statusFilter = signal<LogStatus>('');
  isrcFilter = signal('');

  skeletons = Array(5);

  ngOnInit() {
    this.fetchLogs();
  }

  onStatusChange(event: Event) {
    this.statusFilter.set((event.target as HTMLSelectElement).value as LogStatus);
  }

  onIsrcInput(event: Event) {
    this.isrcFilter.set((event.target as HTMLInputElement).value.toUpperCase());
  }

  applyFilters() {
    this.logs.set([]);
    this.nextCursor.set(null);
    this.fetchLogs();
  }

  loadMore() {
    this.fetchLogs(this.nextCursor() ?? undefined);
  }

  toggleExpand(id: number) {
    this.expandedId.update(current => current === id ? null : id);
  }

  statusLabel(status: string): string {
    const labels: Record<string, string> = {
      pending: 'Pending',
      success: 'Success',
      not_found: 'Not Found',
      failed: 'Failed',
    };
    return labels[status] ?? status;
  }

  formatDuration(ms: number | null): string {
    if (ms === null) return '-';
    if (ms < 1000) return `${ms}ms`;
    return `${(ms / 1000).toFixed(1)}s`;
  }

  private fetchLogs(cursor?: string) {
    const isLoadMore = !!cursor;

    this.loading.set(true);

    this.logService.getLogs({
      status: this.statusFilter(),
      isrc: this.isrcFilter() || undefined,
      per_page: 20,
      cursor,
    }).subscribe({
      next: (res) => {
        if (isLoadMore) {
          this.logs.update(prev => [...prev, ...res.data]);
        } else {
          this.logs.set(res.data);
        }
        this.nextCursor.set(res.meta.next_cursor);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
      },
    });
  }
}
