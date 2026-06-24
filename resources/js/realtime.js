/**
 * Realtime delivery resilience for Pusher clients.
 *
 * Pusher does not replay messages that were published while a client's socket
 * was disconnected. On tablets that sleep, background the tab, or briefly lose
 * wifi, the socket drops silently and any events fired during the gap are lost
 * forever — leaving the screen stale with no error anywhere.
 *
 * attachResync re-runs `onResync` (the same re-fetch the live events trigger)
 * whenever the client recovers:
 *   - the socket re-connects after a drop,
 *   - the tab becomes visible again (tablet wake / tab refocus),
 *   - the browser comes back online.
 * Triggers that fire together are coalesced so we re-fetch once, not three times.
 *
 * Returns a teardown function that removes every listener — call it on component
 * unmount to avoid leaking handlers across remounts.
 *
 * @param {import('pusher-js').default} pusher  an already-created Pusher client
 * @param {() => void} onResync                 re-fetch the screen's live state
 * @param {{ coalesceMs?: number }} [options]
 * @returns {() => void} teardown
 */
export function attachResync(pusher, onResync, options = {}) {
  const coalesceMs = options.coalesceMs ?? 1000
  let lastRun = 0
  let seenConnected = false

  const resync = () => {
    const now = Date.now()
    if (now - lastRun < coalesceMs) return
    lastRun = now
    onResync()
  }

  // Only recover on a genuine RE-connection; the initial connect is already
  // covered by the component's own mount fetch.
  const onConnected = () => {
    if (!seenConnected) {
      seenConnected = true
      return
    }
    resync()
  }
  pusher.connection.bind('connected', onConnected)

  const onVisibility = () => {
    if (document.visibilityState === 'visible') resync()
  }
  document.addEventListener('visibilitychange', onVisibility)

  const onOnline = () => resync()
  window.addEventListener('online', onOnline)

  return () => {
    pusher.connection.unbind('connected', onConnected)
    document.removeEventListener('visibilitychange', onVisibility)
    window.removeEventListener('online', onOnline)
  }
}
