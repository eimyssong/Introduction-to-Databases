#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <limits.h>

#define MAX_KEYS 31
#define MAX_POINTERS 32
#define MIN_KEYS 15

typedef struct Node {
    int is_leaf;
    int num_keys;
    int keys[MAX_KEYS];
    struct Node* pointers[MAX_POINTERS];
    struct Node* parent;
    struct Node* next;
} Node;

Node* root = NULL;

Node* make_node(void);
Node* make_leaf(void);
Node* find_leaf(Node* root, int key);
int find_index(int* arr, int n, int key);
void insert_into_leaf(Node* leaf, int key);
void insert_into_leaf_after_splitting(Node* leaf, int key);
void insert_into_parent(Node* left, int key, Node* right);
void insert_into_node_after_splitting(Node* old_node, int left_index, int key, Node* right);
void start_new_tree(int key);
void insert(int key);
void delete_key(int key);
void remove_entry_from_node(Node* n, int key, Node* pointer);
Node* adjust_root(Node* n);
int get_neighbor_index(Node* n);
Node* coalesce_nodes(Node* n, Node* neighbor, int neighbor_index, int k_prime);
void redistribute_nodes(Node* n, Node* neighbor, int neighbor_index, int k_prime_index, int k_prime);
void range_search(int min, int max);
void print_tree(Node* root);
void print_leaves(Node* root);
void insert_from_file(const char* filename);
void free_tree(Node* n);
void interactive_menu(void);


Node* make_node(void) {
    Node* new_node = (Node*)malloc(sizeof(Node));
    if (!new_node) {
        perror("malloc");
        exit(EXIT_FAILURE);
    }
    new_node->is_leaf = 0;
    new_node->num_keys = 0;
    new_node->parent = NULL;
    new_node->next = NULL;
    for (int i = 0; i < MAX_POINTERS; i++) new_node->pointers[i] = NULL;
    for (int i = 0; i < MAX_KEYS; i++) new_node->keys[i] = 0;
    return new_node;
}

Node* make_leaf(void) {
    Node* leaf = make_node();
    leaf->is_leaf = 1;
    return leaf;
}

int find_index(int* arr, int n, int key) {
    int i = 0;
    while (i < n && arr[i] < key) i++;
    return i;
}

Node* find_leaf(Node* root_node, int key) {
    if (root_node == NULL) return NULL;
    Node* c = root_node;
    while (!c->is_leaf) {
        int i = 0;
        while (i < c->num_keys && key >= c->keys[i]) i++;
        c = c->pointers[i];
    }
    return c;
}

void start_new_tree(int key) {
    root = make_leaf();
    root->keys[0] = key;
    root->num_keys = 1;
    root->parent = NULL;
    root->next = NULL;
}

void insert_into_leaf(Node* leaf, int key) {
    int i;
    for (i = leaf->num_keys; i > 0 && leaf->keys[i-1] > key; i--) {
        leaf->keys[i] = leaf->keys[i-1];
    }
    leaf->keys[i] = key;
    leaf->num_keys++;
}

void insert_into_leaf_after_splitting(Node* leaf, int key) {
    int n = leaf->num_keys;
    int *temp = (int*)malloc(sizeof(int)*(n+1));
    int insert_pos = find_index(leaf->keys, n, key);
    int i, j;

    for (i = 0, j = 0; i < n; i++, j++) {
        if (j == insert_pos) temp[j++] = key;
        temp[j] = leaf->keys[i];
    }
    if (insert_pos == n) temp[n] = key;

    int split = (MAX_KEYS + 1) / 2;
    leaf->num_keys = 0;
    for (i = 0; i < split; i++) {
        leaf->keys[i] = temp[i];
        leaf->num_keys++;
    }

    Node* new_leaf = make_leaf();
    for (i = split, j = 0; i < n + 1; i++, j++) {
        new_leaf->keys[j] = temp[i];
        new_leaf->num_keys++;
    }
    new_leaf->next = leaf->next;
    leaf->next = new_leaf;
    new_leaf->parent = leaf->parent;

    int new_key = new_leaf->keys[0];
    free(temp);
    insert_into_parent(leaf, new_key, new_leaf);
}

void insert_into_parent(Node* left, int key, Node* right) {
    Node* parent = left->parent;
    if (parent == NULL) {
        Node* new_root = make_node();
        new_root->keys[0] = key;
        new_root->pointers[0] = left;
        new_root->pointers[1] = right;
        new_root->num_keys = 1;
        new_root->parent = NULL;
        left->parent = new_root;
        right->parent = new_root;
        root = new_root;
        return;
    }

    int left_index = 0;
    while (left_index <= parent->num_keys && parent->pointers[left_index] != left) left_index++;

    if (parent->num_keys < MAX_KEYS) {
        for (int i = parent->num_keys; i > left_index; i--) {
            parent->pointers[i+1] = parent->pointers[i];
            parent->keys[i] = parent->keys[i-1];
        }
        parent->pointers[left_index+1] = right;
        parent->keys[left_index] = key;
        parent->num_keys++;
        right->parent = parent;
        return;
    }

    insert_into_node_after_splitting(parent, left_index, key, right);
}

void insert_into_node_after_splitting(Node* old_node, int left_index, int key, Node* right) {
    int num = old_node->num_keys;
    int *karr = (int*)malloc(sizeof(int)*(num+1));
    Node** parr = (Node**)malloc(sizeof(Node*)*(num+2));

    for (int i = 0; i < num; i++) karr[i] = old_node->keys[i];
    for (int i = 0; i <= num; i++) parr[i] = old_node->pointers[i];

    for (int i = num; i > left_index; i--) karr[i] = karr[i-1];
    for (int i = num+1; i > left_index+1; i--) parr[i] = parr[i-1];
    karr[left_index] = key;
    parr[left_index+1] = right;

    int split = (MAX_KEYS + 1) / 2;
    Node* new_node = make_node();
    old_node->num_keys = 0;

    for (int i = 0; i < split; i++) {
        old_node->keys[i] = karr[i];
        old_node->pointers[i] = parr[i];
        old_node->num_keys++;
    }
    old_node->pointers[split] = parr[split];

    int k_prime = karr[split];
    int idx = 0;
    for (int i = split + 1; i <= num; i++) {
        new_node->keys[idx] = karr[i];
        new_node->pointers[idx] = parr[i];
        new_node->num_keys++;
        idx++;
    }
    new_node->pointers[idx] = parr[num + 1];

    for (int i = 0; i <= new_node->num_keys; i++) {
        if (new_node->pointers[i]) new_node->pointers[i]->parent = new_node;
    }
    for (int i = 0; i <= old_node->num_keys; i++) {
        if (old_node->pointers[i]) old_node->pointers[i]->parent = old_node;
    }

    new_node->parent = old_node->parent;
    free(karr);
    free(parr);
    insert_into_parent(old_node, k_prime, new_node);
}

void insert(int key) {
    if (root == NULL) {
        start_new_tree(key);
        return;
    }

    Node* leaf = find_leaf(root, key);
    if (!leaf) {
        start_new_tree(key);
        return;
    }

    for (int i = 0; i < leaf->num_keys; i++) {
        if (leaf->keys[i] == key) {
            printf("Value %d already exists; insertion skipped.\n", key);
            return;
        }
    }

    if (leaf->num_keys < MAX_KEYS) {
        insert_into_leaf(leaf, key);
    } else {
        insert_into_leaf_after_splitting(leaf, key);
    }
}

void remove_entry_from_node(Node* n, int key, Node* pointer) {
    int i, num_pointers;
    i = 0;
    while (i < n->num_keys && n->keys[i] != key)
        i++;
    for (++i; i < n->num_keys; i++)
        n->keys[i - 1] = n->keys[i];
    n->num_keys--;

    num_pointers = n->is_leaf ? n->num_keys : n->num_keys + 1;
    i = 0;
    while (i < num_pointers && n->pointers[i] != pointer)
        i++;
    for (++i; i < num_pointers + 1; i++)
        n->pointers[i - 1] = n->pointers[i];
}

Node* adjust_root(Node* n) {
    if (n->num_keys > 0)
        return n;
    if (!n->is_leaf) {
        Node* new_root = n->pointers[0];
        new_root->parent = NULL;
        free(n);
        return new_root;
    }
    free(n);
    return NULL;
}

int get_neighbor_index(Node* n) {
    Node* parent = n->parent;
    for (int i = 0; i <= parent->num_keys; i++) {
        if (parent->pointers[i] == n)
            return i - 1;
    }
    return -1;
}

Node* coalesce_nodes(Node* n, Node* neighbor, int neighbor_index, int k_prime) {
    Node* tmp;
    int i, j;
    if (neighbor_index == -1) {
        tmp = n;
        n = neighbor;
        neighbor = tmp;
    }

    int neighbor_insertion_index = neighbor->num_keys;
    if (!n->is_leaf) {
        neighbor->keys[neighbor_insertion_index] = k_prime;
        neighbor->num_keys++;
        for (i = 0, j = neighbor_insertion_index + 1; i < n->num_keys + 1; i++, j++) {
            neighbor->pointers[j] = n->pointers[i];
            if (neighbor->pointers[j])
                neighbor->pointers[j]->parent = neighbor;
        }
        for (i = 0, j = neighbor_insertion_index + 1; i < n->num_keys; i++, j++)
            neighbor->keys[j] = n->keys[i];
        neighbor->num_keys += n->num_keys;
    } else {
        for (i = 0, j = neighbor_insertion_index; i < n->num_keys; i++, j++)
            neighbor->keys[j] = n->keys[i];
        neighbor->num_keys += n->num_keys;
        neighbor->next = n->next;
    }

    Node* parent = n->parent;
    int k_prime_index = neighbor_index == -1 ? 0 : neighbor_index;
    remove_entry_from_node(parent, parent->keys[k_prime_index], n);
    free(n);

    if (parent == root && parent->num_keys == 0)
        root = adjust_root(parent);
    else if (parent->num_keys < MIN_KEYS)
        delete_key(parent->keys[0]); 
    return root;
}


void redistribute_nodes(Node* n, Node* neighbor, int neighbor_index, int k_prime_index, int k_prime) {
    if (neighbor_index != -1) {
        if (!n->is_leaf) {
            for (int i = n->num_keys; i > 0; i--)
                n->keys[i] = n->keys[i - 1];
            for (int i = n->num_keys + 1; i > 0; i--)
                n->pointers[i] = n->pointers[i - 1];
            n->pointers[0] = neighbor->pointers[neighbor->num_keys];
            if (n->pointers[0])
                n->pointers[0]->parent = n;
            n->keys[0] = k_prime;
            n->parent->keys[k_prime_index] = neighbor->keys[neighbor->num_keys - 1];
        } else {
            for (int i = n->num_keys; i > 0; i--)
                n->keys[i] = n->keys[i - 1];
            n->keys[0] = neighbor->keys[neighbor->num_keys - 1];
            n->parent->keys[k_prime_index] = n->keys[0];
        }
        neighbor->num_keys--;
        n->num_keys++;
    } else {
        if (n->is_leaf) {
            n->keys[n->num_keys] = neighbor->keys[0];
            n->parent->keys[k_prime_index] = neighbor->keys[1];
        } else {
            n->keys[n->num_keys] = k_prime;
            n->pointers[n->num_keys + 1] = neighbor->pointers[0];
            if (n->pointers[n->num_keys + 1])
                n->pointers[n->num_keys + 1]->parent = n;
            n->parent->keys[k_prime_index] = neighbor->keys[0];
        }

        for (int i = 0; i < neighbor->num_keys - 1; i++)
            neighbor->keys[i] = neighbor->keys[i + 1];
        if (!n->is_leaf)
            for (int i = 0; i < neighbor->num_keys; i++)
                neighbor->pointers[i] = neighbor->pointers[i + 1];
        neighbor->num_keys--;
        n->num_keys++;
    }
}

void delete_key(int key) {
    if (root == NULL) {
        printf("Value %d not found; cannot delete.\n", key);
        return;
    }

    Node* leaf = find_leaf(root, key);
    if (!leaf) {
        printf("Value %d not found; cannot delete.\n", key);
        return;
    }

    int found = 0;
    for (int i = 0; i < leaf->num_keys; i++)
        if (leaf->keys[i] == key)
            found = 1;
    if (!found) {
        printf("Value %d not found; cannot delete.\n", key);
        return;
    }

    remove_entry_from_node(leaf, key, NULL);

    if (leaf == root) {
        root = adjust_root(leaf);
        return;
    }

    if (leaf->num_keys >= MIN_KEYS)
        return; 

    int neighbor_index = get_neighbor_index(leaf);
    int k_prime_index = neighbor_index == -1 ? 0 : neighbor_index;
    int k_prime = leaf->parent->keys[k_prime_index];
    Node* neighbor = neighbor_index == -1 ? leaf->parent->pointers[1]
                                          : leaf->parent->pointers[neighbor_index];

    if (neighbor->num_keys + leaf->num_keys <= MAX_KEYS)
        root = coalesce_nodes(leaf, neighbor, neighbor_index, k_prime);
    else
        redistribute_nodes(leaf, neighbor, neighbor_index, k_prime_index, k_prime);
}


void range_search(int min, int max) {
    if (root == NULL) {
        printf("Range search result: (empty tree)\n");
        return;
    }
    Node* leaf = find_leaf(root, min);
    if (!leaf) {
        printf("Range search result: (none)\n");
        return;
    }

    int printed = 0;
    while (leaf) {
        for (int i = 0; i < leaf->num_keys; i++) {
            int k = leaf->keys[i];
            if (k < min) continue;
            if (k > max) {
                if (!printed) printf("(none)\n");
                else printf("\n");
                return;
            }
            if (!printed) { printf("%d", k); printed = 1; }
            else printf(" %d", k);
        }
        leaf = leaf->next;
    }
    if (!printed) printf("(none)");
    printf("\n");
}

void print_tree(Node* root) {
    if (!root) {
        printf("Tree is empty.\n");
        return;
    }
    Node** queue = NULL;
    int head = 0, tail = 0, qcap = 16;
    queue = (Node**)malloc(sizeof(Node*) * qcap);
    queue[tail++] = root;

    while (head < tail) {
        int level_count = tail - head;
        for (int i = 0; i < level_count; i++) {
            Node* n = queue[head++];
            printf("[");
            for (int k = 0; k < n->num_keys; k++) {
                if (k) printf(",");
                printf("%d", n->keys[k]);
            }
            printf("] ");
            if (!n->is_leaf) {
                for (int j = 0; j <= n->num_keys; j++) {
                    if (tail >= qcap) {
                        qcap *= 2;
                        queue = (Node**)realloc(queue, sizeof(Node*) * qcap);
                    }
                    queue[tail++] = n->pointers[j];
                }
            }
        }
        printf("\n");
    }
    free(queue);
}

void print_leaves(Node* root) {
    if (!root) {
        printf("(empty tree)\n");
        return;
    }
    Node* c = root;
    while (!c->is_leaf) c = c->pointers[0];
    while (c) {
        for (int i = 0; i < c->num_keys; i++) printf("%d ", c->keys[i]);
        printf("| ");
        c = c->next;
    }
    printf("\n");
}

void insert_from_file(const char* filename) {
    FILE* fp = fopen(filename, "r");
    if (!fp) {
        perror("File open failed");
        return;
    }
    int x;
    while (fscanf(fp, "%d", &x) == 1) {
        insert(x);
    }
    fclose(fp);
}

void free_tree(Node* n) {
    if (!n) return;
    if (!n->is_leaf) {
        for (int i = 0; i <= n->num_keys; i++) {
            free_tree(n->pointers[i]);
        }
    }
    free(n);
}

void interactive_menu(void) {
    while (1) {
        printf("\nSelect command:\n");
        printf("1: Insert (single)\n");
        printf("2: Delete (single)\n");
        printf("3: Range search (min max)\n");
        printf("4: Print tree (level order)\n");
        printf("5: Print leaves (linked order)\n");
        printf("0: Exit\n");
        printf("Enter choice: ");
        int cmd;
        if (scanf("%d", &cmd) != 1) { while(getchar()!='\n'); continue; }
        if (cmd == 0) break;
        else if (cmd == 1) {
            int v; printf("Value to insert: ");
            if (scanf("%d", &v)!=1) { while(getchar()!='\n'); continue;}
            insert(v);
        } else if (cmd == 2) {
            int v; printf("Value to delete: ");
            if (scanf("%d", &v)!=1) { while(getchar()!='\n'); continue;}
            delete_key(v);
        } else if (cmd == 3) {
            int a,b; printf("min max: ");
            if (scanf("%d %d", &a, &b)!=2) { while(getchar()!='\n'); continue;}
            printf("Range search result (%d ~ %d): ", a, b);
            range_search(a,b);
        } else if (cmd == 4) {
            print_tree(root);
        } else if (cmd == 5) {
            print_leaves(root);
        } else {
            printf("Unknown command.\n");
        }
    }
}

int main(int argc, char* argv[]) {
    if (argc >= 2) {
        insert_from_file(argv[1]);
        printf("Inserted keys from file successfully.\n");
    } else {
        printf("No file provided. Starting with empty tree.\n");
    }

    interactive_menu();
    free_tree(root);
    return 0;
}
