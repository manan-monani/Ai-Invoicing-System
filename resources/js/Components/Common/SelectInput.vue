<script setup lang="ts">
import axios from 'axios';
import { Check, ChevronDown, Search } from 'lucide-vue-next';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

type SelectOption = {
    value: string | number | null;
    label: string;
    disabled?: boolean;
    meta?: Record<string, unknown> | null;
};

const props = withDefaults(
    defineProps<{
        modelValue: string | number | null;
        options: SelectOption[];
        placeholder?: string;
        selectedLabel?: string;
        disabled?: boolean;
        buttonClass?: string;
        listClass?: string;
        optionClass?: string;
        emptyLabel?: string;
        wrapperClass?: string;
        id?: string;
        name?: string;
        fetchUrl?: string | null;
        queryParam?: string;
        minChars?: number;
        debounceMs?: number;
        searchable?: boolean;
        searchPlaceholder?: string;
        loadingLabel?: string;
        allowCustom?: boolean;
        customLabel?: string;
        placement?: 'top' | 'bottom';
    }>(),
    {
        placeholder: 'Select an option',
        options: () => [],
        disabled: false,
        buttonClass: '',
        listClass: '',
        optionClass: '',
        emptyLabel: 'No options available',
        wrapperClass: 'w-full',
        fetchUrl: null,
        queryParam: 'q',
        minChars: 1,
        debounceMs: 300,
        searchable: undefined,
        searchPlaceholder: 'Search...',
        loadingLabel: 'Searching...',
        allowCustom: false,
        customLabel: 'Use',
        placement: 'bottom',
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: string | number | null): void;
    (event: 'change', value: string | number | null): void;
    (event: 'select', option: SelectOption): void;
    (event: 'custom', value: string): void;
    (event: 'open'): void;
    (event: 'close'): void;
}>();

const rootRef = ref<HTMLElement | null>(null);
const searchInputRef = ref<HTMLInputElement | null>(null);
const isOpen = ref(false);
const activeIndex = ref(-1);
const searchTerm = ref('');
const isLoading = ref(false);
const remoteOptions = ref<SelectOption[]>(props.options);
const internalSelectedLabel = ref<string | null>(null);
const isCustomSelection = ref(false);
const requestId = ref(0);
const debounceTimer = ref<ReturnType<typeof setTimeout> | null>(null);

const isAsync = computed(() => Boolean(props.fetchUrl));
const isSearchable = computed(() => props.searchable ?? isAsync.value);
const selectedIndex = computed(() => props.options.findIndex((option) => option.value === props.modelValue));
const selectedOption = computed(() => (selectedIndex.value >= 0 ? props.options[selectedIndex.value] : null));
const displayLabel = computed(() => selectedOption.value?.label ?? internalSelectedLabel.value ?? props.selectedLabel ?? props.placeholder);
const normalizedTerm = computed(() => searchTerm.value.trim());
const hasExactMatch = computed(() => {
    if (!normalizedTerm.value) return false;
    const term = normalizedTerm.value.toLowerCase();
    return displayedOptions.value.some((option) => option.label.toLowerCase() === term);
});
const canCreate = computed(() => props.allowCustom && normalizedTerm.value.length > 0 && !hasExactMatch.value);

const baseButtonClass =
    'w-full px-4 py-2 text-sm border border-slate-200 rounded-xl bg-white text-slate-700 flex items-center justify-between gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/30 transition';

const openMenu = () => {
    if (props.disabled) return;
    isOpen.value = true;
    emit('open');
    nextTick(() => {
        if (isSearchable.value && searchInputRef.value) {
            searchInputRef.value.focus();
        }
        if (selectedIndex.value >= 0) {
            activeIndex.value = selectedIndex.value;
            return;
        }
        activeIndex.value = displayedOptions.value.findIndex((option) => !option.disabled);
    });

    if (isAsync.value) {
        fetchOptions('', true);
    }
};

const closeMenu = () => {
    if (!isOpen.value) return;
    isOpen.value = false;
    activeIndex.value = -1;
    searchTerm.value = '';
    emit('close');
};

const toggleMenu = () => {
    if (isOpen.value) {
        closeMenu();
        return;
    }
    openMenu();
};

const selectOption = (option: SelectOption, index: number) => {
    if (option.disabled) return;
    isCustomSelection.value = false;
    emit('update:modelValue', option.value);
    emit('change', option.value);
    emit('select', option);
    internalSelectedLabel.value = option.label;
    activeIndex.value = index;
    closeMenu();
};

const selectCustom = () => {
    if (!canCreate.value) return;
    const label = normalizedTerm.value;
    if (!label) return;
    isCustomSelection.value = true;
    emit('update:modelValue', null);
    emit('change', null);
    emit('custom', label);
    internalSelectedLabel.value = label;
    closeMenu();
};

const moveActive = (direction: 1 | -1) => {
    const total = displayedOptions.value.length;
    if (!total) return;
    let nextIndex = activeIndex.value;
    for (let i = 0; i < total; i += 1) {
        nextIndex = (nextIndex + direction + total) % total;
        if (!displayedOptions.value[nextIndex]?.disabled) {
            activeIndex.value = nextIndex;
            break;
        }
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (props.disabled) return;
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (!isOpen.value) {
            openMenu();
            return;
        }
        moveActive(1);
        return;
    }
    if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (!isOpen.value) {
            openMenu();
            return;
        }
        moveActive(-1);
        return;
    }
    if (event.key === 'Enter' && isOpen.value) {
        event.preventDefault();
        const option = displayedOptions.value[activeIndex.value];
        if (option) {
            selectOption(option, activeIndex.value);
            return;
        }
        if (canCreate.value) {
            selectCustom();
        }
        return;
    }
    if (event.key === 'Escape' && isOpen.value) {
        event.preventDefault();
        closeMenu();
    }
};

const fetchOptions = async (term: string, allowEmpty = false) => {
    if (!props.fetchUrl) return;
    if (!allowEmpty && term.length < props.minChars) {
        isLoading.value = false;
        remoteOptions.value = props.options;
        return;
    }

    const currentRequest = requestId.value + 1;
    requestId.value = currentRequest;
    isLoading.value = true;

    try {
        const response = await axios.get(props.fetchUrl, {
            params: {
                [props.queryParam]: term,
            },
        });

        if (requestId.value !== currentRequest) return;
        const data = Array.isArray(response.data?.data) ? response.data.data : [];
        remoteOptions.value = data;
    } catch (error) {
        if (requestId.value !== currentRequest) return;
        remoteOptions.value = [];
    } finally {
        if (requestId.value === currentRequest) {
            isLoading.value = false;
        }
    }
};

const scheduleFetch = (term: string) => {
    if (!props.fetchUrl) return;
    if (!isOpen.value) return;
    if (debounceTimer.value) {
        clearTimeout(debounceTimer.value);
    }
    debounceTimer.value = setTimeout(() => {
        fetchOptions(term, term.length === 0);
    }, props.debounceMs);
};

const filteredOptions = computed(() => {
    if (!isSearchable.value) return props.options;
    if (!searchTerm.value) return props.options;
    const term = searchTerm.value.toLowerCase();
    return props.options.filter((option) => option.label.toLowerCase().includes(term));
});

const displayedOptions = computed(() => (isAsync.value ? remoteOptions.value : filteredOptions.value));

const emptyStateLabel = computed(() => {
    if (isLoading.value) return props.loadingLabel;
    if (isAsync.value && searchTerm.value.length > 0 && searchTerm.value.length < props.minChars) {
        return `Type at least ${props.minChars} character${props.minChars === 1 ? '' : 's'} to search`;
    }
    return props.emptyLabel;
});

const handleClickOutside = (event: MouseEvent) => {
    if (!rootRef.value) return;
    if (rootRef.value.contains(event.target as Node)) return;
    closeMenu();
};

watch(
    () => props.modelValue,
    () => {
        if (!isOpen.value) return;
        if (selectedIndex.value >= 0) {
            activeIndex.value = selectedIndex.value;
        }
    },
);

watch(
    () => props.modelValue,
    (value) => {
        if (value === null || value === '' || value === undefined) {
            if (!isCustomSelection.value) {
                internalSelectedLabel.value = null;
            }
        }
        if (value !== null && value !== '' && value !== undefined) {
            isCustomSelection.value = false;
        }
    },
);

watch(
    () => props.selectedLabel,
    (value) => {
        if (value) {
            internalSelectedLabel.value = null;
            isCustomSelection.value = false;
        }
    },
);

watch(searchTerm, (value) => {
    if (!isAsync.value) return;
    scheduleFetch(value);
});

watch(
    () => props.options,
    (value) => {
        if (isAsync.value && !searchTerm.value) {
            remoteOptions.value = value;
        }
    },
);

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="rootRef" class="relative" :class="wrapperClass">
        <button
            :id="id"
            type="button"
            :disabled="disabled"
            :name="name"
            :class="[baseButtonClass, buttonClass, disabled ? 'cursor-not-allowed bg-slate-100 text-slate-400 opacity-60' : '']"
            aria-haspopup="listbox"
            :aria-expanded="isOpen"
            @click="toggleMenu"
            @keydown="handleKeydown"
        >
            <span :class="selectedOption || internalSelectedLabel || props.selectedLabel ? 'text-slate-700' : 'text-slate-400'" class="truncate">
                {{ displayLabel }}
            </span>
            <ChevronDown class="h-4 w-4 text-slate-400 transition-transform duration-150" :class="isOpen ? 'rotate-180' : ''" />
        </button>

        <transition
            enter-active-class="transition ease-out duration-150"
            :enter-from-class="placement === 'top' ? 'opacity-0 translate-y-1' : 'opacity-0 -translate-y-1'"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0"
            :leave-to-class="placement === 'top' ? 'opacity-0 translate-y-1' : 'opacity-0 -translate-y-1'"
        >
            <div
                v-if="isOpen"
                class="absolute z-50 w-full rounded-xl border border-slate-200 bg-white shadow-xl"
                :class="[placement === 'top' ? 'bottom-full mb-2' : 'mt-2', listClass]"
            >
                <div v-if="isSearchable" class="border-b border-slate-100 p-2">
                    <div class="relative">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            ref="searchInputRef"
                            v-model="searchTerm"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="w-full rounded-lg border border-slate-200 py-2 pr-3 pl-9 text-sm focus:ring-2 focus:ring-brand-500/20 focus:outline-none"
                        />
                    </div>
                </div>
                <ul role="listbox" class="max-h-60 overflow-auto py-1">
                    <li v-if="canCreate" class="px-2 py-1">
                        <button
                            type="button"
                            class="w-full rounded-lg border border-dashed border-slate-200 px-3 py-2 text-left text-sm text-slate-600 hover:bg-slate-50"
                            @click="selectCustom"
                        >
                            {{ props.customLabel }} "{{ normalizedTerm }}"
                        </button>
                    </li>
                    <li v-if="!displayedOptions.length" class="px-3 py-2 text-xs text-slate-400">
                        {{ emptyStateLabel }}
                    </li>
                    <li v-for="(option, index) in displayedOptions" :key="`${option.value}-${index}`">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-3 py-2 text-left text-sm transition-colors"
                            :class="[
                                option.disabled ? 'cursor-not-allowed text-slate-300' : 'text-slate-700 hover:bg-slate-50',
                                index === activeIndex ? 'bg-slate-50' : '',
                                option.value === modelValue ? 'font-semibold text-brand-600' : '',
                                optionClass,
                            ]"
                            :disabled="option.disabled"
                            @click="selectOption(option, index)"
                        >
                            <span class="truncate">{{ option.label }}</span>
                            <Check v-if="option.value === modelValue" class="h-4 w-4 text-brand-500" />
                        </button>
                    </li>
                </ul>
            </div>
        </transition>
    </div>
</template>
