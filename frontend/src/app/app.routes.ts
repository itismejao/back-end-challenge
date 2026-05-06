import { Routes } from '@angular/router';
import { TrackListComponent } from './components/track-list/track-list.component';
import { IntegrationLogsComponent } from './components/integration-logs/integration-logs.component';

export const routes: Routes = [
  { path: '', component: TrackListComponent },
  { path: 'logs', component: IntegrationLogsComponent },
];
