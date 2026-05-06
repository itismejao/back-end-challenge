import { Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { TrackService } from '../../services/track.service';
import { Track, OrderBy, Direction } from '../../models/track.model';
import { MarketSelectorComponent } from '../market-selector/market-selector.component';
import { FiltersComponent } from '../filters/filters.component';
import { TrackCardComponent } from '../track-card/track-card.component';
import { TrackSkeletonComponent } from '../track-skeleton/track-skeleton.component';

@Component({
  selector: 'app-track-list',
  imports: [RouterLink, MarketSelectorComponent, FiltersComponent, TrackCardComponent, TrackSkeletonComponent],
  template: `
    <div class="container">
      <header class="header">
        <div class="header-left">
          <h1 class="logo">OneRPM</h1>
          <span class="subtitle">Track Explorer</span>
        </div>
        <div class="header-right">
          <a routerLink="/logs" class="logs-link">Logs</a>
          <app-filters (filterChange)="onFilterChange($event)" />
          <app-market-selector (marketChange)="onMarketChange($event)" />
        </div>
      </header>

      <main class="track-list">
        @if (initialLoading()) {
          @for (_ of skeletons; track $index) {
            <app-track-skeleton />
          }
        }

        @if (!initialLoading() && tracks().length === 0) {
          <div class="empty">
            <span class="empty-icon">&#127925;</span>
            <p>Nenhuma faixa encontrada</p>
          </div>
        }

        @for (track of tracks(); track track.id) {
          <app-track-card [track]="track" />
        }

        @if (loadingMore()) {
          @for (_ of skeletons; track $index) {
            <app-track-skeleton />
          }
        }

        @if (nextCursor() && !loadingMore()) {
          <button class="load-more" (click)="loadMore()">
            Carregar mais
          </button>
        }
      </main>
    </div>
  `,
  styles: [`
    .container {
      max-width: 900px; margin: 0 auto; padding: 24px 16px;
    }

    .header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 32px; flex-wrap: wrap; gap: 16px;
    }
    .header-left { display: flex; align-items: baseline; gap: 12px; }
    .header-right { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }

    .logs-link {
      color: #999; text-decoration: none; font-size: 14px;
      padding: 8px 16px; border: 1px solid #333; border-radius: 8px;
      transition: color 0.2s, border-color 0.2s;
    }
    .logs-link:hover { color: #1db954; border-color: #1db954; }

    .logo {
      margin: 0; font-size: 28px; font-weight: 800; color: #1db954;
      letter-spacing: -0.5px;
    }
    .subtitle { color: #666; font-size: 14px; }

    .track-list { display: flex; flex-direction: column; gap: 8px; }

    .empty {
      text-align: center; padding: 64px 0; color: #666;
    }
    .empty-icon { font-size: 48px; display: block; margin-bottom: 16px; }

    .load-more {
      margin: 24px auto 0; padding: 12px 32px; border: 1px solid #333;
      border-radius: 24px; background: none; color: #fff;
      font-size: 14px; font-weight: 600; cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
    }
    .load-more:hover { border-color: #1db954; background: rgba(29, 185, 84, 0.1); }
  `],
})
export class TrackListComponent {
  private trackService = inject(TrackService);

  tracks = signal<Track[]>([]);
  initialLoading = signal(false);
  loadingMore = signal(false);
  nextCursor = signal<string | null>(null);

  skeletons = Array(5);

  private market = 'BR';
  private orderBy: OrderBy = 'title';
  private direction: Direction = 'asc';

  ngOnInit() {
    this.fetchTracks();
  }

  onMarketChange(market: string) {
    this.market = market;
    this.resetAndFetch();
  }

  onFilterChange(filter: { order_by: OrderBy; direction: Direction }) {
    this.orderBy = filter.order_by;
    this.direction = filter.direction;
    this.resetAndFetch();
  }

  loadMore() {
    this.fetchTracks(this.nextCursor() ?? undefined);
  }

  private resetAndFetch() {
    this.tracks.set([]);
    this.nextCursor.set(null);
    this.fetchTracks();
  }

  private fetchTracks(cursor?: string) {
    const isLoadMore = !!cursor;

    if (isLoadMore) {
      this.loadingMore.set(true);
    } else {
      this.initialLoading.set(true);
    }

    this.trackService.getTracks({
      market: this.market,
      order_by: this.orderBy,
      direction: this.direction,
      per_page: 5,
      cursor,
    }).subscribe({
      next: (res) => {
        if (isLoadMore) {
          this.tracks.update(prev => [...prev, ...res.data]);
        } else {
          this.tracks.set(res.data);
        }
        this.nextCursor.set(res.meta.next_cursor);
        this.initialLoading.set(false);
        this.loadingMore.set(false);
      },
      error: () => {
        this.initialLoading.set(false);
        this.loadingMore.set(false);
      },
    });
  }
}
