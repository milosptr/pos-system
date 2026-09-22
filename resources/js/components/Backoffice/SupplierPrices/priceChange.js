export const DIRECTION_UP = 'up'
export const DIRECTION_DOWN = 'down'

// A price that rose from a recorded zero has no percentage to show, but it did
// rise: without this it would carry an arrow in the row and still be counted
// among the articles that never moved.
export function changeDirection(article) {
  if (article.change === null) {
    return article.changed ? DIRECTION_UP : null
  }

  return article.change > 0 ? DIRECTION_UP : DIRECTION_DOWN
}
