export interface Track {
  id: number;
  isrc: string;
  title: string;
  duration: string;
  duration_ms: number;
  explicit: boolean;
  disc_number: number;
  track_number: number;
  available: boolean;
  market: string;
  album: {
    name: string;
    type: string;
    release_date: string | null;
    thumb: string | null;
  };
  artists: { id: number; name: string }[];
  spotify: {
    external_id: string | null;
    url: string | null;
  };
}

export interface TrackResponse {
  data: Track[];
  meta: {
    path: string;
    per_page: number;
    next_cursor: string | null;
    prev_cursor: string | null;
  };
}

export type OrderBy = 'title' | 'duration' | 'release_date' | 'artist' | 'track_number' | 'created_at';
export type Direction = 'asc' | 'desc';
