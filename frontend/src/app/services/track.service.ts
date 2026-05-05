import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { TrackResponse, OrderBy, Direction } from '../models/track.model';

@Injectable({ providedIn: 'root' })
export class TrackService {
  private http = inject(HttpClient);

  getTracks(params: {
    market: string;
    order_by?: OrderBy;
    direction?: Direction;
    per_page?: number;
    cursor?: string;
  }): Observable<TrackResponse> {
    let httpParams = new HttpParams().set('market', params.market);

    if (params.order_by) httpParams = httpParams.set('order_by', params.order_by);
    if (params.direction) httpParams = httpParams.set('direction', params.direction);
    if (params.per_page) httpParams = httpParams.set('per_page', params.per_page.toString());
    if (params.cursor) httpParams = httpParams.set('cursor', params.cursor);

    return this.http.get<TrackResponse>('/api/tracks', { params: httpParams });
  }
}
