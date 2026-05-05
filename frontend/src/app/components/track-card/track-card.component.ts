import { Component, input, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { Track } from '../../models/track.model';

@Component({
  selector: 'app-track-card',
  imports: [DatePipe],
  template: `
    <div class="card">
      <div class="card-left">
        @if (track().album.thumb) {
          <img [src]="track().album.thumb" [alt]="track().album.name" class="thumb" />
        } @else {
          <div class="thumb placeholder">&#9835;</div>
        }
      </div>

      <div class="card-center">
        <div class="title-row">
          <h3 class="title">{{ track().title }}</h3>
          @if (track().explicit) {
            <span class="badge explicit">E</span>
          }
          <span class="badge availability" [class.available]="track().available" [class.unavailable]="!track().available">
            {{ marketFlag() }} {{ track().available ? 'Disponivel' : 'Indisponivel' }}
          </span>
        </div>

        <p class="artists">{{ artistNames() }}</p>

        <div class="meta">
          <span class="album-name">{{ track().album.name }}</span>
          <span class="dot">&middot;</span>
          <span>{{ track().album.release_date | date:'dd/MM/yyyy' }}</span>
          <span class="dot">&middot;</span>
          <span>{{ track().duration }}</span>
        </div>

        @if (track().spotify.external_id && showPlayer()) {
          <div class="player">
            <iframe
              [src]="embedUrl()"
              width="100%"
              height="80"
              frameborder="0"
              allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
              loading="lazy"
            ></iframe>
          </div>
        }
      </div>

      <div class="card-right">
        @if (track().spotify.external_id) {
          <button class="play-btn" (click)="togglePlayer()" [title]="showPlayer() ? 'Fechar player' : 'Preview'">
            {{ showPlayer() ? '\u2715' : '\u25B6' }}
          </button>
        }

        @if (track().spotify.url) {
          <a [href]="track().spotify.url" target="_blank" rel="noopener" class="spotify-link" title="Abrir no Spotify">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="#1db954">
              <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
            </svg>
          </a>
        }
      </div>
    </div>
  `,
  styles: [`
    .card {
      display: flex; align-items: flex-start; gap: 20px;
      padding: 20px; background: #181818; border-radius: 12px;
      transition: background 0.2s;
    }
    .card:hover { background: #282828; }

    .thumb {
      width: 80px; height: 80px; border-radius: 8px;
      object-fit: cover; flex-shrink: 0;
    }
    .thumb.placeholder {
      display: flex; align-items: center; justify-content: center;
      background: #333; color: #666; font-size: 32px;
    }

    .card-center { flex: 1; min-width: 0; }

    .title-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .title { margin: 0; font-size: 18px; color: #fff; font-weight: 600; }

    .badge {
      padding: 2px 8px; border-radius: 4px; font-size: 11px;
      font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
    }
    .explicit { background: #666; color: #fff; }
    .availability.available { background: rgba(29, 185, 84, 0.15); color: #1db954; }
    .availability.unavailable { background: rgba(255, 85, 85, 0.15); color: #ff5555; }

    .artists { margin: 4px 0; color: #b3b3b3; font-size: 14px; }

    .meta {
      display: flex; align-items: center; gap: 6px; margin-top: 4px;
      color: #666; font-size: 13px;
    }
    .album-name { color: #999; }
    .dot { color: #444; }

    .player { margin-top: 12px; border-radius: 8px; overflow: hidden; }

    .card-right {
      display: flex; flex-direction: column; align-items: center; gap: 12px;
      flex-shrink: 0; padding-top: 4px;
    }

    .play-btn {
      width: 40px; height: 40px; border-radius: 50%;
      border: none; background: #1db954; color: #000;
      font-size: 16px; cursor: pointer; display: flex;
      align-items: center; justify-content: center;
      transition: transform 0.1s, background 0.2s;
    }
    .play-btn:hover { transform: scale(1.05); background: #1ed760; }

    .spotify-link {
      opacity: 0.6; transition: opacity 0.2s;
    }
    .spotify-link:hover { opacity: 1; }
  `],
})
export class TrackCardComponent {
  track = input.required<Track>();
  showPlayer = signal(false);

  private sanitizer: DomSanitizer;

  constructor(sanitizer: DomSanitizer) {
    this.sanitizer = sanitizer;
  }

  marketFlag(): string {
    const code = this.track().market;
    if (!code || code.length !== 2) return '';
    return String.fromCodePoint(
      ...[...code.toUpperCase()].map(c => 0x1F1E6 + c.charCodeAt(0) - 65)
    );
  }

  artistNames(): string {
    return this.track().artists.map(a => a.name).join(', ');
  }

  togglePlayer() {
    this.showPlayer.update(v => !v);
  }

  embedUrl(): SafeResourceUrl {
    return this.sanitizer.bypassSecurityTrustResourceUrl(
      `https://open.spotify.com/embed/track/${this.track().spotify.external_id}?utm_source=generator&theme=0`
    );
  }
}
