import { Component, output, signal } from '@angular/core';
import { OrderBy, Direction } from '../../models/track.model';

@Component({
  selector: 'app-filters',
  template: `
    <div class="filters">
      <div class="filter-group">
        <label>Ordenar por</label>
        <select [value]="orderBy()" (change)="onOrderChange($event)">
          <option value="title">Título</option>
          <option value="artist">Artista</option>
          <option value="duration">Duração</option>
          <option value="release_date">Lançamento</option>
          <option value="track_number">Faixa</option>
          <option value="created_at">Adicionado</option>
        </select>
      </div>

      <button class="direction-btn" (click)="toggleDirection()" [title]="direction() === 'asc' ? 'Crescente' : 'Decrescente'">
        {{ direction() === 'asc' ? '↑' : '↓' }}
      </button>
    </div>
  `,
  styles: [`
    .filters {
      display: flex; align-items: center; gap: 12px;
    }

    .filter-group {
      display: flex; align-items: center; gap: 8px;
    }

    label { color: #999; font-size: 13px; white-space: nowrap; }

    select {
      padding: 8px 12px; border: 1px solid #333; border-radius: 8px;
      background: #1e1e1e; color: #fff; font-size: 14px;
      cursor: pointer; outline: none;
    }
    select:focus { border-color: #1db954; }

    .direction-btn {
      width: 36px; height: 36px; border: 1px solid #333; border-radius: 8px;
      background: #1e1e1e; color: #fff; font-size: 18px;
      cursor: pointer; display: flex; align-items: center; justify-content: center;
      transition: border-color 0.2s;
    }
    .direction-btn:hover { border-color: #1db954; }
  `],
})
export class FiltersComponent {
  filterChange = output<{ order_by: OrderBy; direction: Direction }>();

  orderBy = signal<OrderBy>('title');
  direction = signal<Direction>('asc');

  onOrderChange(event: Event) {
    this.orderBy.set((event.target as HTMLSelectElement).value as OrderBy);
    this.emit();
  }

  toggleDirection() {
    this.direction.update(d => d === 'asc' ? 'desc' : 'asc');
    this.emit();
  }

  private emit() {
    this.filterChange.emit({ order_by: this.orderBy(), direction: this.direction() });
  }
}
