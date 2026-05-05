import { Component, output, signal } from '@angular/core';

interface Market {
  code: string;
  name: string;
  flag: string;
}

@Component({
  selector: 'app-market-selector',
  template: `
    <div class="market-selector">
      <button class="market-btn" (click)="toggleDropdown()">
        <span class="flag">{{ selected().flag }}</span>
        <span class="code">{{ selected().code }}</span>
        <span class="arrow" [class.open]="open()">&#9662;</span>
      </button>

      @if (open()) {
        <div class="dropdown">
          @for (market of markets; track market.code) {
            <button
              class="dropdown-item"
              [class.active]="market.code === selected().code"
              (click)="select(market)"
            >
              <span class="flag">{{ market.flag }}</span>
              <span>{{ market.name }}</span>
            </button>
          }
        </div>
      }
    </div>
  `,
  styles: [`
    .market-selector { position: relative; display: inline-block; }

    .market-btn {
      display: flex; align-items: center; gap: 8px;
      padding: 8px 16px; border: 1px solid #333;
      border-radius: 8px; background: #1e1e1e; color: #fff;
      cursor: pointer; font-size: 14px; transition: border-color 0.2s;
    }
    .market-btn:hover { border-color: #1db954; }

    .flag { font-size: 22px; }
    .arrow { font-size: 10px; transition: transform 0.2s; }
    .arrow.open { transform: rotate(180deg); }

    .dropdown {
      position: absolute; top: calc(100% + 4px); left: 0;
      background: #282828; border: 1px solid #333; border-radius: 8px;
      overflow: hidden; z-index: 100; min-width: 180px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    }

    .dropdown-item {
      display: flex; align-items: center; gap: 10px; width: 100%;
      padding: 10px 16px; border: none; background: none;
      color: #ccc; cursor: pointer; font-size: 14px; text-align: left;
    }
    .dropdown-item:hover { background: #333; color: #fff; }
    .dropdown-item.active { color: #1db954; }
  `],
})
export class MarketSelectorComponent {
  marketChange = output<string>();

  markets: Market[] = [
    { code: 'BR', name: 'Brasil', flag: '🇧🇷' },
    { code: 'US', name: 'United States', flag: '🇺🇸' },
    { code: 'GB', name: 'United Kingdom', flag: '🇬🇧' },
    { code: 'DE', name: 'Deutschland', flag: '🇩🇪' },
    { code: 'JP', name: '日本', flag: '🇯🇵' },
    { code: 'FR', name: 'France', flag: '🇫🇷' },
    { code: 'ES', name: 'España', flag: '🇪🇸' },
    { code: 'AR', name: 'Argentina', flag: '🇦🇷' },
  ];

  selected = signal(this.markets[0]);
  open = signal(false);

  toggleDropdown() {
    this.open.update(v => !v);
  }

  select(market: Market) {
    this.selected.set(market);
    this.open.set(false);
    this.marketChange.emit(market.code);
  }
}
