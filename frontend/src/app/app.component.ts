import { Component } from '@angular/core';
import { TrackListComponent } from './components/track-list/track-list.component';

@Component({
  selector: 'app-root',
  imports: [TrackListComponent],
  template: '<app-track-list />',
  styles: [],
})
export class AppComponent {}
