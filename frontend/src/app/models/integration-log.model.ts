export interface IntegrationLog {
  id: number;
  provider_code: string;
  isrc: string;
  status: 'pending' | 'success' | 'not_found' | 'failed';
  duration_ms: number | null;
  attempt: number;
  markets: string[] | null;
  error_message: string | null;
  error_class: string | null;
  track: { id: number; name: string; isrc: string } | null;
  started_at: string | null;
  finished_at: string | null;
}

export interface IntegrationLogResponse {
  data: IntegrationLog[];
  meta: {
    path: string;
    per_page: number;
    next_cursor: string | null;
    prev_cursor: string | null;
  };
}

export type LogStatus = 'pending' | 'success' | 'not_found' | 'failed' | '';
