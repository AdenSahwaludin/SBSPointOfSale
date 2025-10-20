import { ref } from 'vue';

export function useDebouncedSearch<T = any>(options: {
  wait?: number;
  minLength?: number;
  search: (q: string) => Promise<T[] | void> | T[] | void;
  onResults?: (res: T[]) => void;
}) {
  const wait = options.wait ?? 300;
  const minLength = options.minLength ?? 2;

  const query = ref('');
  const isSearching = ref(false);
  const results = ref<T[]>([]);
  let timer: number | null = null;

  const clearTimer = () => {
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }
  };

  const clear = () => {
    clearTimer();
    query.value = '';
    results.value = [];
    isSearching.value = false;
  };

  const trigger = async () => {
    const q = query.value.trim();
    if (q.length < minLength) {
      results.value = [];
      isSearching.value = false;
      return;
    }
    isSearching.value = true;
    try {
      const res = await options.search(q);
      if (Array.isArray(res)) {
        results.value = res;
        options.onResults?.(res);
      }
    } finally {
      isSearching.value = false;
    }
  };

  const onInput = () => {
    clearTimer();
    const q = query.value.trim();
    if (q.length < minLength) {
      results.value = [];
      isSearching.value = false;
      return;
    }
    isSearching.value = true;
    timer = window.setTimeout(() => {
      trigger();
    }, wait);
  };

  return { query, isSearching, results, onInput, clear, trigger };
}

