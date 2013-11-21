Welcome, Contributor!
=====================

On behalf of the entire Fluid Powered TYPO3 team, thank you for wanting to contribute to our
projects - by creating issues, pull requests or just asking great support questions which
can help other users in the future.

This is the *ultra-compact* guide for that.

## Submitting Issues - Feature Requests, Bug Reports, Support Questions...

You have heard all this before, but just in case:

* Before submitting bugs, make sure the bug you are reporting...
  - Is not already fixed (if in doubt: the Git master branch is always up-to-date)
  - Is not currently reported and/or under review
* When submitting bugs, include information about what you wanted to achieve and which errors
  or messages (reminder: error log messages are worth a thousand words) you encountered.

In short: We want to trust that your report is valid and contains helpful information. This
allows us to be most efficient when assisting or fixing the problem; everything runs smoother.

## Contributing Code

In order to ensure that your contribution passes through with flying colors (we have automated
tests for all of these factors) there are a few things you can do.

* Follow the coding guidelines - viewing any class will give you a clear picture of our
  standards which we always enforce. Each extension has perfect or near-perfect guidelines
  compliance and contributed code should not lower compliance. If you spot a violation in the
  code, we of course also appreciate contributions which do nothing but fix cosmetics!
* Use a valid commit message subject. When you write your commit messages (note: the ones you
  write in Git itself, not the same as the Pull Request cover message!) always start your
  message with one of [DOC], [BUGFIX], [TASK] or [FEATURE] to describe the nature of your
  commit's changes.
* Make one commit per change and one change per commit only. Example: if you are going to create
  a particular feature and this feature requires a few changes to existing code to prepare for
  the feature, first make individual commits with the required changes and then create your
  "real" work on top of this.
* Recommendation: plan a few steps ahead, try to group your work into logical "chunks" of code
  which are easier to manage, then commit each "chunk" of work as you finish it. There is one
  time management strategy worth mentioning in this context: the [Pomodoro][pomodoro] technique.

## Making Pull requests

Please make sure, you read the entire [Contribution Guide][contributionGuide] in advance.

So, you have prepared a nice bufix to the latest version of flux and want that to be available
to anybody? Great! As we assume, you consumed the [Contribution Guide][contributionGuide]
to make your contribution follow best practices, everything should be fine.

Really?

In reality, we tend to be very picky concerning commit messages and our CGL. - Sir Travis
is also. He checks every commit message and wants to make sure, you applied the CGL to your
code. So what if he-or we-complain about findings in your code? Let us try to explain by
example:

### Example: The wrong commit message

So you hit the "wrong commit message" issue and we "force" you to change your commit 
message(s). What now? GIT is magic but not that easy to learn. Lets say you have 2 commits.
This is what it would look like in the Pull-Request:

```
Author - Message (commit hash)
------------------------------
Cedric Ziel - a lead-haxor addition to flUx! (aaaaa)
Cedric Ziel - Fixing a docs-issue (bbbbb)
```

What's wrong? You know it-because you read the [guide][contributionGuide]! The commit
(I reference them by hash) aaaaa is lacking a prefix and an uppercase letter at the
beginning.

The optimal structure would be:

```
Author - Message (commit hash)
------------------------------
Cedric Ziel - [FEATURE] New property x added
Cedric Ziel - [DOC] Documenting feature y
```

So you need to ``reword`` (git slang) your last two commits.

To do this, you need to issue a ``rebase``:
```
git rebase -i HEAD~2 # this will issue an interactive rebase of the last 2 commits off from HEAD
# an editor will come up, showing your last 2 commits, change the first words from pick to reword
# save & exit
# another editor will come up for both commits, make the topic changes there, save & exit
# A message should be shown the rebase is complete
git push --force $GITHUBREMOTE $FEATUREBRANCHNAME 
# $GITHUBREMOTE is most likely "origin" in your case if you didnt change anything
# $FEATUREBRANCHNAME is the name of your branch you made the changes on
# this will overwrite the remote's history entirely and the force is absolutely neccessary there
# The changes will shop up here immediately and Travis will try to build again
```

The full guide is available at http://fedext.net/overview/contributing/contribution-guide.html

Last words: welcome to the growing list of contributors! :)

[contributionGuide]: https://github.com/FluidTYPO3/flux.git "FluidTYPO3 contribution guide"
[pomodoro]: http://www.pomodorotechnique.com/ "The Pomodory Technique"
