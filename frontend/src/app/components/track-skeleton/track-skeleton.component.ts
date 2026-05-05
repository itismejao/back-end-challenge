import { Component } from '@angular/core';

@Component({
  selector: 'app-track-skeleton',
  template: `
    <div class="skeleton-card">
      <div class="skeleton-thumb skeleton-pulse"></div>
      <div class="skeleton-content">
        <div class="skeleton-title skeleton-pulse"></div>
        <div class="skeleton-artists skeleton-pulse"></div>
        <div class="skeleton-meta">
          <div class="skeleton-album skeleton-pulse"></div>
          <div class="skeleton-duration skeleton-pulse"></div>
        </div>
      </div>
      <div class="skeleton-actions">
        <div class="skeleton-play skeleton-pulse"></div>
        <div class="skeleton-link skeleton-pulse"></div>
      </div>
    </div>
  `,
  styles: [`
    .skeleton-card {
      display: flex; align-items: flex-start; gap: 20px;
      padding: 20px; background: #181818; border-radius: 12px;
    }

    .skeleton-pulse {
      background: linear-gradient(90deg, #282828 25%, #333 50%, #282828 75%);
      background-size: 200% 100%;
      animation: pulse 1.5s ease-in-out infinite;
      border-radius: 4px;
    }

    @keyframes pulse {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    .skeleton-thumb {
      width: 80px; height: 80px; border-radius: 8px; flex-shrink: 0;
    }

    .skeleton-content {
      flex: 1; display: flex; flex-direction: column; gap: 8px;
    }

    .skeleton-title { height: 20px; width: 60%; }
    .skeleton-artists { height: 14px; width: 40%; }

    .skeleton-meta {
      display: flex; gap: 12px; margin-top: 2px;
    }
    .skeleton-album { height: 13px; width: 120px; }
    .skeleton-duration { height: 13px; width: 45px; }

    .skeleton-actions {
      display: flex; flex-direction: column; align-items: center; gap: 12px;
      flex-shrink: 0; padding-top: 4px;
    }
    .skeleton-play { width: 40px; height: 40px; border-radius: 50%; }
    .skeleton-link { width: 24px; height: 24px; border-radius: 50%; }
  `],
})
export class TrackSkeletonComponent {}
