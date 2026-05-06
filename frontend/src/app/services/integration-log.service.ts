import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { IntegrationLogResponse, LogStatus } from '../models/integration-log.model';

@Injectable({ providedIn: 'root' })
export class IntegrationLogService {
  private http = inject(HttpClient);

  getLogs(params: {
    status?: LogStatus;
    isrc?: string;
    provider_code?: string;
    per_page?: number;
    cursor?: string;
  }): Observable<IntegrationLogResponse> {
    let httpParams = new HttpParams();

    if (params.status) httpParams = httpParams.set('status', params.status);
    if (params.isrc) httpParams = httpParams.set('isrc', params.isrc);
    if (params.provider_code) httpParams = httpParams.set('provider_code', params.provider_code);
    if (params.per_page) httpParams = httpParams.set('per_page', params.per_page.toString());
    if (params.cursor) httpParams = httpParams.set('cursor', params.cursor);

    return this.http.get<IntegrationLogResponse>('/api/integration/logs', { params: httpParams });
  }
}
