/*
 * surun
 *    surun user command
 *
 * gcc -o surun surun.c
 * chmod u+s surun
 */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/types.h>
#include <unistd.h>
#include <errno.h>
#include <pwd.h>
#include <signal.h>

#define DEBUG           0
#define COMMAND_MAXLEN  256

int main(int argc, char *argv[])
{
  char           *user;
  char           *command;

  sigset_t       sigset;
  sigset_t       sigsave;
  struct passwd  *pw_ent;
  //char           cmd[COMMAND_MAXLEN+100];

  int            ret;

  if (argc == 1) {
    fprintf(stderr, "Usage: %s user command\n", argv[0]);
    exit(0);
  }
  if (argc != 3) {
    fprintf(stderr, "ERROR: the wrong number of arguments\n");
    exit(1);
  }

  user = argv[1];
  command = argv[2];

  if (sigfillset(&sigset) || sigprocmask(SIG_BLOCK, &sigset, &sigsave)) {
    fprintf(stderr, "ERROR sigfillset, reason : %s\n", strerror(errno));
    exit(1);
  }

  if ((pw_ent = getpwnam(user)) == NULL) {
    fprintf(stderr, "ERROR invalid user. user name %s.\n", user);
    exit(4);
  }

/*
  // if it is root or an error, then it is an error
  if (pw_ent->pw_uid < 1) {
    fprintf(stderr, "ERROR invalid user name - %s.\n", user);
    exit(4);
  }
*/

  ret = setgid(pw_ent->pw_gid);
  if (ret != 0) {
    fprintf(stderr, "ERROR could not setgid %d.\n", pw_ent->pw_gid);
    exit(8);
  }

#if DEBUG
  // before setuid()
  printf("before uid=%d\n", getuid());
#endif

  // setuid to the specified localuser
  ret = setuid(pw_ent->pw_uid);
  //ret = setuid(0);
  if (ret != 0) {
    fprintf(stderr, "ERROR could not setuid %d.\n", pw_ent->pw_uid);
    exit(5);
  }

#if DEBUG
  // after setuid()
  printf("after uid=%d\n", getuid());
#endif

  // run
#if DEBUG
  printf("command: %s\n", command);
  printf("----- start of result -----\n");
#endif
  // execute it
  ret = system(command);
#if DEBUG
  printf("----- end of result -----\n");
  printf("return code=%d\n", ret);
#endif


}

